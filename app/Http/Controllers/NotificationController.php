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

        if (! $this->canAccessNotification($user, $notification)) {
            return redirect()->route('portal.dashboard')->with('status', 'That notification is no longer available.');
        }

        // Mark as read if not already read
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        $data = $notification->data ?? [];
        $url = $this->resolveNotificationUrl($data);

        return redirect()->to(is_string($url) && $url !== '' ? $url : route('portal.dashboard'));
    }

    public function markRead(Request $request, PortalNotification $notification)
    {
        $user = Auth::user();
        if (! $this->canAccessNotification($user, $notification)) {
            return redirect()->route('portal.dashboard')->with('status', 'That notification is no longer available.');
        }

        $notification->update(['read_at' => now()]);

        return back()->with('status', 'Notification marked read.');
    }

    public function markUnread(Request $request, PortalNotification $notification)
    {
        $user = Auth::user();
        if (! $this->canAccessNotification($user, $notification)) {
            return redirect()->route('portal.dashboard')->with('status', 'That notification is no longer available.');
        }

        $notification->update(['read_at' => null]);

        return back()->with('status', 'Notification marked unread.');
    }

    private function canAccessNotification($user, PortalNotification $notification): bool
    {
        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'system_admin'], true)) {
            return true;
        }

        return (int) ($notification->user_id ?? 0) === (int) $user->id
            || (int) ($notification->recipient_id ?? 0) === (int) $user->id;
    }

    private function resolveNotificationUrl(array $data): ?string
    {
        $url = $data['url'] ?? null;

        if (! empty($data['message_id'])) {
            return route('portal.messages.conversation.from_message', ['message' => $data['message_id']]);
        }

        if (empty($url) && ! empty($data['conversation_id'])) {
            return route('portal.admin.support.conversation.show', ['conversation' => $data['conversation_id']]);
        }

        return is_string($url) && $url !== '' ? $url : null;
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
