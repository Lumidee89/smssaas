<?php

namespace App\Http\Controllers;

use App\Events\AnnouncementCreated;
use App\Models\Announcement;
use App\Services\Notifications\ParentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('school_id', Auth::user()->school_id)->with('author')->latest()->paginate(20);

        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'body' => ['required', 'string', 'max:10000'], 'audience' => ['required', Rule::in(['all', 'parents', 'students', 'teachers'])], 'publish_now' => ['nullable', 'boolean'], 'expires_at' => ['nullable', 'date', 'after:now']]);
        $announcement = Announcement::create(['school_id' => Auth::user()->school_id, 'author_id' => Auth::id(), 'title' => $data['title'], 'body' => $data['body'], 'audience' => $data['audience'], 'published_at' => ($data['publish_now'] ?? false) ? now() : null, 'expires_at' => $data['expires_at'] ?? null]);
        if ($announcement->published_at) {
            AnnouncementCreated::dispatch($announcement);
        }

        return back()->with('success', 'Announcement created successfully.');
    }

    public function publish(Announcement $announcement)
    {
        $this->tenant($announcement);
        $wasPublished = $announcement->published_at !== null;
        $announcement->update(['published_at' => now()]);
        if (! $wasPublished) {
            AnnouncementCreated::dispatch($announcement);
        }

        return back()->with('success', 'Announcement published.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->tenant($announcement);
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    private function tenant(Announcement $announcement): void
    {
        abort_unless($announcement->school_id === Auth::user()->school_id, 403);
    }

}
