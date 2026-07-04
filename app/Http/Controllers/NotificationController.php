<?php

namespace App\Http\Controllers;

use App\Models\PortalNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = PortalNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();

        PortalNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }

    public function show(Request $request, PortalNotification $notification)
    {
        $user = Auth::user();
        // Allow owners or admins/system_admins to view the notification
        if ($notification->user_id !== $user->id && ! in_array($user->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        // Mark as read if not already read
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        $data = $notification->data ?? [];
        $url = $data['url'] ?? null;

        if (empty($url) && ! empty($data['conversation_id'])) {
            $url = route('portal.admin.support.conversation.show', ['conversation' => $data['conversation_id']]);
        }

        return redirect($url ?? route('portal.dashboard'));
    }

    public function markRead(Request $request, PortalNotification $notification)
    {
        $user = Auth::user();
        if ($notification->user_id !== $user->id && ! in_array($user->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return back()->with('status', 'Notification marked read.');
    }

    public function markUnread(Request $request, PortalNotification $notification)
    {
        $user = Auth::user();
        if ($notification->user_id !== $user->id && ! in_array($user->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        $notification->update(['read_at' => null]);

        return back()->with('status', 'Notification marked unread.');
    }

    public function preferences()
    {
        $user = Auth::user();
        $pref = UserNotificationPreference::firstOrCreate(['user_id' => $user->id]);

        return view('notifications.preferences', compact('pref'));
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'channel_email' => ['sometimes', 'boolean'],
            'channel_in_app' => ['sometimes', 'boolean'],
            'channel_push' => ['sometimes', 'boolean'],
            'channel_sms' => ['sometimes', 'boolean'],
            'events' => ['nullable', 'array'],
        ]);

        $pref = UserNotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'channel_email' => $request->boolean('channel_email'),
                'channel_in_app' => $request->boolean('channel_in_app'),
                'channel_push' => $request->boolean('channel_push'),
                'channel_sms' => $request->boolean('channel_sms'),
                'events' => $data['events'] ?? null,
            ]
        );

        return back()->with('status', 'Preferences updated.');
    }
}
