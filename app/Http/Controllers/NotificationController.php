<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\PortalNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $unreadCount = PortalNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        PortalNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['status' => 'success', 'count' => 0, 'cleared' => $unreadCount]);
        }

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
        $url = $this->resolveNotificationUrl($user, $data);

        Log::debug('NotificationController@show resolved URL', [
            'route' => request()->route()?->getName(),
            'user_id' => $user->id,
            'notification_id' => $notification->id,
            'notification_data' => $data,
            'resolved_url' => $url,
        ]);

        if (is_string($url) && $url !== '') {
            return redirect()->to($url);
        }

        return redirect()->route('portal.dashboard');
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

    private function resolveNotificationUrl($user, array $data): ?string
    {
        $url = $data['url'] ?? null;
        if (empty($url) && ! empty($data['action_url'])) {
            $url = $data['action_url'];
        }

        if (! empty($data['message_id'])) {
            $message = Message::find($data['message_id']);

            if (! $message) {
                Log::warning('NotificationController@resolveNotificationUrl missing message', [
                    'user_id' => $user?->id,
                    'message_id' => $data['message_id'],
                    'notification_data' => $data,
                ]);
                return null;
            }

            if (! $this->canAccessMessage($user, $message)) {
                Log::warning('NotificationController@resolveNotificationUrl unauthorized message access', [
                    'user_id' => $user?->id,
                    'message_id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'user_role' => $user?->role,
                ]);
                return null;
            }

            return route($this->notificationRoutePrefix().'conversation.from_message', ['message' => $message->id]);
        }

        if (empty($url) && ! empty($data['conversation_id'])) {
            return route('portal.admin.support.conversation.show', ['conversation' => $data['conversation_id']]);
        }

        return $this->sanitizeNotificationRedirectUrl(is_string($url) && $url !== '' ? $url : null);
    }

    private function sanitizeNotificationRedirectUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (preg_match('#^/portal/admin/invoices/(\d+)/(review|reject|pay)(?:/.*)?$#', $path, $matches)) {
            return route('portal.admin.invoices.show', $matches[1]);
        }

        return $url;
    }

    private function canAccessMessage($user, Message $message): bool
    {
        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'system_admin'], true)) {
            return true;
        }

        return $message->recipient_id === $user->id || $message->sender_id === $user->id;
    }

    private function notificationRoutePrefix(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'portal.messages.';
        }

        return $user->role === 'participant'
            ? 'portal.participant.messages.'
            : 'portal.messages.';
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
