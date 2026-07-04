<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Participant;
use App\Models\User;
use App\Models\Worker;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // ===== MESSAGE TEMPLATES =====

    public function templatesIndex()
    {
        $this->syncDefaultMessageTemplates();

        $templates = MessageTemplate::orderBy('type')->orderByDesc('updated_at')->paginate(20);

        return view('portal.admin.messages.templates.index', compact('templates'));
    }

    public function createTemplate()
    {
        return view('portal.admin.messages.templates.create', [
            'themes' => self::availableTemplateThemes(),
            'themeHtmlDefaults' => MessageService::getDefaultThemeHtmlTemplates(),
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $themeKeys = implode(',', array_keys(self::availableTemplateThemes()));

        $validated = $request->validate([
            'name' => 'required|string|unique:message_templates|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:general,alert,notification,compliance,care_review',
            'category' => 'nullable|string|max:100',
            'theme' => 'required|in:'.$themeKeys,
            'custom_style' => 'nullable|string',
            'theme_html' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        MessageTemplate::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('portal.admin.messages.templates.index')
            ->with('status', 'Message template created successfully.');
    }

    public function editTemplate(MessageTemplate $template)
    {
        return view('portal.admin.messages.templates.edit', [
            'template' => $template,
            'themes' => self::availableTemplateThemes(),
            'themeHtmlDefaults' => MessageService::getDefaultThemeHtmlTemplates(),
        ]);
    }

    public function updateTemplate(Request $request, MessageTemplate $template)
    {
        $themeKeys = implode(',', array_keys(self::availableTemplateThemes()));

        $validated = $request->validate([
            'name' => 'required|string|unique:message_templates,name,'.$template->id.'|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:general,alert,notification,compliance,care_review',
            'category' => 'nullable|string|max:100',
            'theme' => 'required|in:'.$themeKeys,
            'custom_style' => 'nullable|string',
            'theme_html' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('portal.admin.messages.templates.index')
            ->with('status', 'Message template updated successfully.');
    }

    public function deleteTemplate(MessageTemplate $template)
    {
        $template->delete();

        return redirect()->route('portal.admin.messages.templates.index')
            ->with('status', 'Message template deleted successfully.');
    }

    // ===== SEND MESSAGES =====

    public function sendIndex()
    {
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();
        $templates = MessageTemplate::where('is_active', true)->orderBy('type')->get();

        return view('portal.admin.messages.send', compact('users', 'templates'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'recipient_ids' => 'required|array|min:1',
            'recipient_ids.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_id' => 'nullable|exists:message_templates,id',
        ]);

        $senderId = Auth::id();
        $template = null;

        if (! empty($validated['template_id'])) {
            $template = MessageTemplate::where('id', $validated['template_id'])
                ->where('is_active', true)
                ->first();

            if (! $template) {
                return back()->withErrors(['template_id' => 'The selected template is not active or cannot be used.']);
            }
        }

        try {
            DB::beginTransaction();

            foreach ($validated['recipient_ids'] as $recipientId) {
                if ($template) {
                    MessageService::sendMessageUsingTemplate($senderId, (int) $recipientId, $template);
                } else {
                    MessageService::sendMessage($senderId, (int) $recipientId, $validated['subject'], $validated['body'], null);
                }
            }

            DB::commit();

            $count = count($validated['recipient_ids']);

            return redirect()->route('portal.admin.messages.sent')
                ->with('status', "Message sent successfully to {$count} recipient(s).");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to send message: '.$e->getMessage()]);
        }
    }

    // ===== INBOX & SENT =====

    public function compose(?int $recipient = null)
    {
        $user = Auth::user();
        $recipients = collect();
        $selectedRecipient = null;

        if ($user->role === 'participant') {
            $participant = Participant::where('user_id', $user->id)
                ->with(['assignments.worker.user'])
                ->firstOrFail();

            $recipients = $participant->assignments
                ->where('status', 'active')
                ->pluck('worker')
                ->filter()
                ->map->user
                ->filter()
                ->unique('id')
                ->values();
        } elseif ($user->role === 'worker') {
            $worker = Worker::where('user_id', $user->id)
                ->with(['assignments.participant.user'])
                ->firstOrFail();

            $recipients = $worker->assignments
                ->where('status', 'active')
                ->pluck('participant')
                ->filter()
                ->map->user
                ->filter()
                ->unique('id')
                ->values();
        } else {
            abort(403, 'Direct messaging is not available for your account.');
        }

        if ($recipient) {
            $selectedRecipient = $recipients->firstWhere('id', $recipient);
        }

        return view('portal.messages.compose', compact('recipients', 'selectedRecipient'));
    }

    private function syncDefaultMessageTemplates(): void
    {
        if (MessageTemplate::count() > 0) {
            return;
        }

        $defaults = [
            [
                'name' => 'Welcome Message',
                'subject' => 'Welcome to {{organization}}',
                'body' => "Dear {{first_name}},\n\nWelcome to our portal! We're excited to have you join us.\n\nThis message is to confirm that your account has been successfully created.\n\nIf you have any questions, please don't hesitate to reach out to our support team.\n\nBest regards,\n{{organization}} Team",
                'type' => 'notification',
                'category' => 'Onboarding',
                'theme' => 'clean',
                'custom_style' => '',
                'theme_html' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Important Update',
                'subject' => 'Important Update Notification',
                'body' => "Dear {{first_name}},\n\nWe wanted to inform you about an important update regarding your account and services.\n\nPlease review the details below and let us know if you have any questions.\n\nThank you for your attention to this matter.\n\nBest regards,\n{{organization}} Team",
                'type' => 'alert',
                'category' => 'Notifications',
                'theme' => 'modern',
                'custom_style' => '',
                'theme_html' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Compliance Document Due',
                'subject' => 'Compliance Document Expiring Soon',
                'body' => "Dear {{first_name}},\n\nThis is a reminder that your compliance documentation will expire on {{date}}.\n\nPlease ensure that you submit the required documents in a timely manner to avoid any disruption to your services.\n\nThank you,\n{{organization}} Team",
                'type' => 'compliance',
                'category' => 'Compliance',
                'theme' => 'corporate',
                'custom_style' => '',
                'theme_html' => '',
                'is_active' => true,
            ],
        ];

        foreach ($defaults as $template) {
            MessageTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }

    private static function availableTemplateThemes(): array
    {
        return [
            'clean' => 'Clean',
            'modern' => 'Modern',
            'warm' => 'Warm',
            'elegant' => 'Elegant',
            'corporate' => 'Corporate',
            'minimal' => 'Minimal',
            'bold' => 'Bold',
            'soft' => 'Soft',
            'premium' => 'Premium',
            'festive' => 'Festive',
            'sunrise' => 'Sunrise',
            'twilight' => 'Twilight',
            'calm' => 'Calm',
            'vibrant' => 'Vibrant',
            'pastel' => 'Pastel',
            'classic' => 'Classic',
            'tech' => 'Tech',
            'luxury' => 'Luxury',
            'natural' => 'Natural',
            'sleek' => 'Sleek',
            'custom' => 'Custom',
        ];
    }

    protected function getDirectChatRecipients()
    {
        $user = Auth::user();

        if ($user->role === 'participant') {
            $participant = Participant::where('user_id', $user->id)
                ->with(['assignments.worker.user'])
                ->firstOrFail();

            return $participant->assignments
                ->where('status', 'active')
                ->pluck('worker')
                ->filter()
                ->map->user
                ->filter()
                ->unique('id')
                ->values();
        }

        if ($user->role === 'worker') {
            $worker = Worker::where('user_id', $user->id)
                ->with(['assignments.participant.user'])
                ->firstOrFail();

            return $worker->assignments
                ->where('status', 'active')
                ->pluck('participant')
                ->filter()
                ->map->user
                ->filter()
                ->unique('id')
                ->values();
        }

        return collect();
    }

    protected function authorizeDirectChatRecipient(User $recipient)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'You must be signed in to access this conversation.');
        }

        if (in_array($user->role, ['admin', 'system_admin'], true)) {
            return;
        }

        $allowed = $this->getDirectChatRecipients()->pluck('id');
        $hasThreadAccess = Message::where(function ($query) use ($user, $recipient) {
            $query->where('sender_id', $user->id)->where('recipient_id', $recipient->id);
        })->orWhere(function ($query) use ($user, $recipient) {
            $query->where('sender_id', $recipient->id)->where('recipient_id', $user->id);
        })->exists();

        if ($allowed->contains($recipient->id) || $hasThreadAccess) {
            return;
        }

        abort(403, 'You are not authorized to chat with this user.');
    }

    public function conversation(User $recipient)
    {
        $this->authorizeDirectChatRecipient($recipient);

        $userId = Auth::id();
        $messages = Message::where(function ($query) use ($userId, $recipient) {
            $query->where('sender_id', $userId)->where('recipient_id', $recipient->id);
        })
            ->orWhere(function ($query) use ($userId, $recipient) {
                $query->where('sender_id', $recipient->id)->where('recipient_id', $userId);
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        Message::where('sender_id', $recipient->id)
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('portal.messages.conversation', compact('recipient', 'messages'));
    }

    public function conversationFromMessage(Message $message)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (! in_array($user->role, ['admin', 'system_admin'], true)
            && $message->recipient_id !== $user->id
            && $message->sender_id !== $user->id
        ) {
            abort(403);
        }

        if (in_array($user->role, ['admin', 'system_admin'], true)
            && $message->recipient_id !== $user->id
            && $message->sender_id !== $user->id
        ) {
            return $this->show($message);
        }

        $recipientId = $message->sender_id === $user->id ? $message->recipient_id : $message->sender_id;
        $recipient = User::findOrFail($recipientId);

        if ($message->recipient_id === $user->id) {
            $message->markAsRead();
        }

        return $this->conversation($recipient);
    }

    public function conversationMessages(User $recipient)
    {
        $this->authorizeDirectChatRecipient($recipient);

        $userId = Auth::id();
        $messages = Message::where(function ($query) use ($userId, $recipient) {
            $query->where('sender_id', $userId)->where('recipient_id', $recipient->id);
        })
            ->orWhere(function ($query) use ($userId, $recipient) {
                $query->where('sender_id', $recipient->id)->where('recipient_id', $userId);
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'text' => $message->body,
                'is_me' => $message->sender_id === $userId,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'author' => $message->sender->name,
            ]),
        ]);
    }

    public function conversationSend(Request $request, User $recipient)
    {
        $this->authorizeDirectChatRecipient($recipient);

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $message = MessageService::sendMessage(
            Auth::id(),
            $recipient->id,
            'Chat message',
            $validated['message']
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => [
                'id' => $message->id,
                'text' => $message->body,
                'is_me' => true,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'author' => Auth::user()->name,
            ]], 201);
        }

        return redirect()->route('portal.messages.conversation', $recipient->id)->with('status', 'Message sent successfully.');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $user = Auth::user();
        $recipientId = $validated['recipient_id'];
        $allowedRecipients = collect();

        if ($user->role === 'participant') {
            $participant = Participant::where('user_id', $user->id)
                ->with(['assignments.worker.user'])
                ->firstOrFail();

            $allowedRecipients = $participant->assignments
                ->where('status', 'active')
                ->pluck('worker')
                ->filter()
                ->map->user
                ->filter()
                ->pluck('id');
        } elseif ($user->role === 'worker') {
            $worker = Worker::where('user_id', $user->id)
                ->with(['assignments.participant.user'])
                ->firstOrFail();

            $allowedRecipients = $worker->assignments
                ->where('status', 'active')
                ->pluck('participant')
                ->filter()
                ->map->user
                ->filter()
                ->pluck('id');
        } else {
            abort(403, 'Direct messaging is not available for your account.');
        }

        if (! $allowedRecipients->contains($recipientId)) {
            abort(403, 'You are not authorized to message this user.');
        }

        MessageService::sendMessage(Auth::id(), $recipientId, $validated['subject'], $validated['body']);

        return redirect()->route('portal.messages.inbox')
            ->with('status', 'Your message has been sent.');
    }

    public function inbox()
    {
        $userId = Auth::id();
        $messages = MessageService::getUserInbox($userId, 20);
        $unreadCount = MessageService::getUnreadCount($userId);

        $view = Auth::user()->role === 'admin' ? 'portal.admin.messages.inbox' : 'portal.messages.inbox';

        return view($view, compact('messages', 'unreadCount'));
    }

    public function sent()
    {
        $userId = Auth::id();
        $messages = MessageService::getUserSentMessages($userId, 20);

        $view = Auth::user()->role === 'admin' ? 'portal.admin.messages.sent' : 'portal.messages.sent';

        return view($view, compact('messages'));
    }

    public function show(Message $message)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (! $this->canAccessMessage($message)) {
            if (in_array($user->role, ['admin', 'system_admin'], true)) {
                return redirect()->route('portal.messages.conversation.from_message', ['message' => $message->id]);
            }

            abort(403);
        }

        if ($message->recipient_id === $user->id) {
            $message->markAsRead();
        }

        $view = $user->role === 'admin' ? 'portal.admin.messages.show' : 'portal.messages.show';

        return view($view, compact('message'));
    }

    public function markRead(Message $message)
    {
        if (! $this->canAccessMessage($message)) {
            abort(403);
        }

        $message->markAsRead();

        return back()->with('status', 'Message marked as read.');
    }

    public function markUnread(Message $message)
    {
        if (! $this->canAccessMessage($message)) {
            abort(403);
        }

        $message->markAsUnread();

        return back()->with('status', 'Message marked as unread.');
    }

    public function delete(Message $message)
    {
        if (! $this->canAccessMessage($message)) {
            abort(403);
        }

        $message->delete();

        return back()->with('status', 'Message deleted.');
    }

    private function canAccessMessage(Message $message): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'system_admin'], true)) {
            return true;
        }

        return $message->recipient_id === $user->id || $message->sender_id === $user->id;
    }

    // ===== ADMIN MESSAGE BROADCAST =====

    public function broadcastIndex()
    {
        $roles = ['participant', 'worker', 'support_person'];
        $templates = MessageTemplate::where('is_active', true)->orderBy('type')->get();

        return view('portal.admin.messages.broadcast', compact('roles', 'templates'));
    }

    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:participant,worker,support_person',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $recipients = User::whereIn('role', $validated['roles'])
            ->where('status', 'active')
            ->get();

        $senderId = Auth::id();
        $count = 0;

        try {
            DB::beginTransaction();

            foreach ($recipients as $recipient) {
                MessageService::sendMessage($senderId, $recipient->id, $validated['subject'], $validated['body']);
                $count++;
            }

            DB::commit();

            return redirect()->route('portal.admin.messages.sent')
                ->with('status', "Broadcast message sent to {$count} user(s).");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to broadcast message: '.$e->getMessage()]);
        }
    }
}
