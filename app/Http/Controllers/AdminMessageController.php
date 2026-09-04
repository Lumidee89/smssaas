<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminMessageController extends Controller
{
    public function index(): View
    {
        $conversations = Auth::user()->conversations()
            ->where('conversations.school_id', Auth::user()->school_id)
            ->whereHas('participants', fn ($query) => $query->where('role', 'parent'))
            ->with(['creator:id,name,role', 'participants:id,name,role', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->latest('last_message_at')
            ->paginate(25);

        return view('messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeConversation($conversation);
        $conversation->participants()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);
        $conversation->load(['participants:id,name,role,email,phone', 'messages.sender:id,name,role']);
        $parent = $conversation->participants->firstWhere('role', 'parent');

        return view('messages.show', compact('conversation', 'parent'));
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        DB::transaction(function () use ($conversation, $data): void {
            Message::create([
                'school_id' => Auth::user()->school_id,
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'body' => $data['body'],
            ]);
            $conversation->update(['last_message_at' => now()]);
        });

        return back()->with('success', 'Reply sent to the parent.');
    }

    private function authorizeConversation(Conversation $conversation): void
    {
        abort_unless(
            $conversation->school_id === Auth::user()->school_id
            && $conversation->participants()->where('users.id', Auth::id())->exists(),
            403,
        );
    }
}
