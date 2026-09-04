<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->paginate(30);

        return response()->json([
            'data' => $notifications->getCollection()->map(fn ($notification) => [
                'id' => $notification->id,
                ...$notification->data,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
            ]),
            'meta' => ['current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage()],
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function registerDevice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', Rule::in(['android', 'ios', 'web'])],
        ]);
        $user = $request->user();
        DeviceToken::updateOrCreate(['token' => $data['token']], [
            'school_id' => $user->school_id,
            'user_id' => $user->id,
            'platform' => $data['platform'],
            'last_seen_at' => now(),
        ]);

        return response()->json(['message' => 'Device registered.'], 201);
    }

    public function unregisterDevice(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:512']]);
        DeviceToken::where('user_id', $request->user()->id)->where('token', $data['token'])->delete();

        return response()->json(['message' => 'Device unregistered.']);
    }
}
