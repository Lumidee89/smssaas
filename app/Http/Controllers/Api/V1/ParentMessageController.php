<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\SchoolRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentMessageController extends Controller
{
    public function contacts(Request $request): JsonResponse
    {
        $contacts = User::where('school_id', $request->user()->school_id)
            ->whereIn('role', SchoolRole::MESSAGE_CONTACTS)->orderBy('role')->orderBy('name')
            ->get(['id', 'name', 'role'])->map(fn ($contact) => [
                'id' => $contact->id, 'name' => $contact->name, 'role' => $contact->role,
                'role_label' => SchoolRole::label($contact->role),
            ]);

        return response()->json(['data' => $contacts]);
    }

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->conversations()->where('conversations.school_id', $request->user()->school_id)->with(['participants:id,name,role', 'messages' => fn ($q) => $q->latest()->limit(1)])->latest('last_message_at')->get();

        return response()->json(['data' => $items->map(fn ($c) => ['id' => $c->id, 'subject' => $c->subject, 'participants' => $c->participants->where('id', '!=', $request->user()->id)->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role])->values(), 'last_message' => $c->messages->first()?->body, 'last_message_at' => $c->last_message_at?->toISOString()])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['recipient_id' => ['nullable', 'integer'], 'subject' => ['required', 'string', 'max:150'], 'body' => ['required', 'string', 'max:5000']]);
        $contacts = User::where('school_id', $request->user()->school_id)->whereIn('role', SchoolRole::MESSAGE_CONTACTS);
        $recipient = filled($data['recipient_id'] ?? null)
            ? (clone $contacts)->find($data['recipient_id'])
            : (clone $contacts)->where('role', SchoolRole::SCHOOL_ADMIN)->orderBy('id')->first();
        abort_unless($recipient, 422, 'The selected school contact is unavailable.');

        $conversation = DB::transaction(function () use ($request, $data, $recipient) {
            $c = Conversation::create(['school_id' => $request->user()->school_id, 'subject' => $data['subject'], 'created_by' => $request->user()->id, 'last_message_at' => now()]);
            $c->participants()->attach([$request->user()->id, $recipient->id]);
            Message::create(['school_id' => $request->user()->school_id, 'conversation_id' => $c->id, 'sender_id' => $request->user()->id, 'body' => $data['body']]);

            return $c;
        });

        return response()->json(['data' => ['id' => $conversation->id]], 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);
        $conversation->participants()->updateExistingPivot($request->user()->id, ['last_read_at' => now()]);
        $messages = $conversation->messages()->with('sender:id,name,role')->oldest()->paginate(50);

        return response()->json(['data' => collect($messages->items())->map(fn ($m) => ['id' => $m->id, 'body' => $m->body, 'sender' => ['id' => $m->sender->id, 'name' => $m->sender->name, 'role' => $m->sender->role], 'created_at' => $m->created_at->toISOString()]), 'meta' => ['current_page' => $messages->currentPage(), 'last_page' => $messages->lastPage(), 'total' => $messages->total()]]);
    }

    public function reply(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $message = DB::transaction(function () use ($request, $conversation, $data) {
            $m = Message::create(['school_id' => $request->user()->school_id, 'conversation_id' => $conversation->id, 'sender_id' => $request->user()->id, 'body' => $data['body']]);
            $conversation->update(['last_message_at' => now()]);

            return $m;
        });

        return response()->json(['data' => ['id' => $message->id, 'body' => $message->body, 'created_at' => $message->created_at->toISOString()]], 201);
    }

    private function authorizeMember(Request $request, Conversation $conversation): void
    {
        abort_unless($conversation->school_id === $request->user()->school_id && $conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);
    }
}
