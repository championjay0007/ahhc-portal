<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\SupportResponse;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    /**
     * Show support tickets list for participant
     */
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal.participant.support.index', compact('tickets'));
    }

    /**
     * Show form to create new support ticket
     */
    public function create()
    {
        return view('portal.participant.support.create');
    }

    /**
     * Store new support ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|min:5|max:200',
            'description' => 'required|string|min:20|max:5000',
            'category' => 'required|in:general,billing,technical,account,other',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        return $this->createTicketForUser(
            Auth::id(),
            $validated['subject'],
            $validated['description'],
            $validated['category'],
            $validated['priority']
        );
    }

    public function storeFromWidget(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ]);
        // If the requester is logged in, attribute the conversation to them.
        if (Auth::check()) {
            $supportUser = Auth::user();
        } else {
            $supportUser = $this->resolvePublicVisitorUser();
        }

        $displayName = $validated['name'] ?? $supportUser->name ?? 'Website visitor';
        $displayEmail = $validated['email'] ?? $supportUser->email ?? null;

        $subject = 'Website support request from '.$displayName;

        $description = trim((string) ($validated['message'] ?? ''));
        if (! empty($validated['name'])) {
            $description = "Visitor name: {$validated['name']}\n\n".$description;
        }
        if (! empty($validated['email'])) {
            $description .= "\n\nContact email: {$validated['email']}";
        }

        $conversation = SupportConversation::create([
            'user_id' => $supportUser->id,
            'subject' => $subject,
            'submitted_name' => $displayName,
            'submitted_email' => $displayEmail,
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'public_token' => Str::uuid()->toString(),
        ]);

        $initialMessage = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => $supportUser->id,
            'message' => $description,
            'is_admin' => false,
        ]);

        $initialMessage->refresh();

        $this->notifyAdminsAboutWidgetConversation($conversation, $supportUser->name);

        return response()->json([
            'message' => 'Support chat started. Our team will respond shortly.',
            'conversation' => [
                'id' => $conversation->id,
                'token' => $conversation->public_token,
                'subject' => $conversation->subject,
                'messages' => [
                    [
                        'id' => $initialMessage->id,
                        'text' => $initialMessage->message,
                        'is_admin' => false,
                        'created_at' => $initialMessage->created_at->format('Y-m-d H:i:s'),
                        'author' => $supportUser->name,
                        'status' => $this->resolveMessageStatus($initialMessage),
                    ],
                ],
            ],
        ]);
    }

    protected function notifyAdminsAboutWidgetConversation(SupportConversation $conversation, string $visitorName): void
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'New Website Support Conversation',
                'message' => "New website support conversation from {$visitorName}: {$conversation->subject}",
                'data' => [
                    'conversation_id' => $conversation->id,
                    'url' => route('portal.admin.support.conversation.show', $conversation),
                ],
            ]);
        }
    }

    public function showWidgetConversation(Request $request, SupportConversation $conversation)
    {
        $this->validateWidgetToken($conversation, $request->query('token'));

        $messages = $conversation->messages()->with('user')->orderBy('created_at', 'asc')->get();

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'status' => $conversation->status,
                'last_message_at' => $conversation->last_message_at?->format('Y-m-d H:i:s'),
            ],
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'text' => $message->message,
                'is_admin' => $message->is_admin,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'author' => $message->user ? $message->user->name : 'Support',
                'status' => $this->resolveMessageStatus($message),
            ]),
        ]);
    }

    public function widgetConversationMessage(Request $request, SupportConversation $conversation)
    {
        $this->validateWidgetToken($conversation, $request->query('token'));

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'message' => $validated['message'],
            'is_admin' => false,
        ]);

        $message->refresh();

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'waiting',
        ]);

        $recipientAdmins = User::where('role', 'admin');
        foreach ($recipientAdmins->get() as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'New Website Support Message',
                'message' => 'A website visitor sent another message in "'.$conversation->subject.'"',
                'data' => [
                    'conversation_id' => $conversation->id,
                    'url' => route('portal.admin.support.conversation.show', $conversation),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent. A support agent will reply shortly.',
            'conversation' => [
                'id' => $conversation->id,
                'token' => $conversation->public_token,
                'subject' => $conversation->subject,
            ],
            'message_record' => [
                'id' => $message->id,
                'text' => $message->message,
                'is_admin' => $message->is_admin,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'author' => $conversation->user->name ?? 'Support',
                'status' => $this->resolveMessageStatus($message),
            ],
        ], 201);
    }

    /**
     * Show a standalone widget view for a conversation (public token required).
     */
    public function showWidgetView(Request $request, SupportConversation $conversation)
    {
        $this->validateWidgetToken($conversation, $request->query('token'));

        $messages = $conversation->messages()->with('user')->orderBy('created_at', 'asc')->get();

        return view('support.widget-view', compact('conversation', 'messages'));
    }

    /**
     * Store a widget conversation from an authenticated user
     */
    public function storeFromWidgetAuthenticated(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $user = Auth::user();

        $displayName = $validated['name'] ?? $user->name ?? 'Website visitor';
        $displayEmail = $validated['email'] ?? $user->email ?? null;

        $subject = 'Website support request from '.$displayName;

        $description = trim((string) ($validated['message'] ?? ''));
        if (! empty($validated['name'])) {
            $description = "Visitor name: {$validated['name']}\n\n".$description;
        }
        if (! empty($validated['email'])) {
            $description .= "\n\nContact email: {$validated['email']}";
        }

        $conversation = SupportConversation::create([
            'user_id' => $user->id,
            'subject' => $subject,
            'submitted_name' => $displayName,
            'submitted_email' => $displayEmail,
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'public_token' => null,
        ]);

        $initialMessage = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'message' => $description,
            'is_admin' => false,
        ]);

        $initialMessage->refresh();

        // Notify admins
        $this->notifyAdminsAboutWidgetConversation($conversation, $displayName);

        return response()->json([
            'message' => 'Support chat started. Our team will respond shortly.',
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'messages' => [
                    [
                        'id' => $initialMessage->id,
                        'text' => $initialMessage->message,
                        'is_admin' => false,
                        'created_at' => $initialMessage->created_at->format('Y-m-d H:i:s'),
                        'author' => $displayName,
                        'status' => $this->resolveMessageStatus($initialMessage),
                    ],
                ],
            ],
        ]);
    }

    protected function validateWidgetToken(SupportConversation $conversation, ?string $token): void
    {
        if (! $token || ! hash_equals($conversation->public_token ?? '', $token)) {
            abort(403, 'Invalid conversation token.');
        }
    }

    protected function resolveMessageStatus(SupportMessage $message): string
    {
        if ($message->read_at) {
            return 'seen';
        }

        if ($message->is_admin) {
            return 'delivered';
        }

        return 'sent';
    }

    protected function resolvePublicVisitorUser(): User
    {
        return User::firstOrCreate([
            'email' => 'website-support@ahhc.com.au',
        ], [
            'name' => 'Website Visitor',
            'role' => 'participant',
            'status' => 'active',
            'password' => Str::random(32),
            'mfa_enabled' => false,
        ]);
    }

    protected function createTicketForUser($userId, string $subject, string $description, string $category, string $priority, string $createdByName = 'Website visitor')
    {
        $ticket = SupportTicket::create([
            'user_id' => $userId,
            'subject' => $subject,
            'description' => $description,
            'category' => $category,
            'priority' => $priority,
            'status' => 'open',
        ]);

        $notificationMessage = Auth::check()
            ? 'New support ticket from '.Auth::user()->name.': '.$subject
            : 'New support ticket from '.$createdByName.': '.$subject;

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'New Support Ticket',
                'message' => $notificationMessage,
                'data' => ['ticket_id' => $ticket->id],
            ]);
        }

        if (! Auth::check()) {
            return response()->json(['message' => 'Support request sent. Our team will get back to you shortly.']);
        }

        return redirect()->route('portal.support.show', $ticket)
            ->with('status', 'Support ticket created successfully. Our team will respond shortly.');
    }

    /**
     * Show support ticket details
     */
    public function show(SupportTicket $ticket)
    {
        // Check if user owns this ticket or is admin
        if (Auth::user()->role !== 'admin' && $ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $responses = $ticket->responses()->orderBy('created_at', 'asc')->get();

        return view('portal.participant.support.show', compact('ticket', 'responses'));
    }

    /**
     * Add response to support ticket
     */
    public function addResponse(Request $request, SupportTicket $ticket)
    {
        // Check if user owns this ticket or is admin
        if (Auth::user()->role !== 'admin' && $ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:5|max:5000',
        ]);

        $response = SupportResponse::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin' => Auth::user()->role === 'admin',
        ]);

        // Update ticket status to in-progress if first response
        if ($ticket->responses()->count() === 1) {
            $ticket->status = 'in-progress';
            $ticket->save();
        }

        // Notify the other party
        if (Auth::user()->role === 'admin') {
            NotificationService::notify([
                'user_id' => $ticket->user_id,
                'title' => 'Support Ticket Response',
                'message' => 'Our support team has responded to your ticket: '.$ticket->subject,
                'data' => ['ticket_id' => $ticket->id],
            ]);
        } else {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                NotificationService::notify([
                    'user_id' => $admin->id,
                    'title' => 'Support Ticket Response',
                    'message' => Auth::user()->name.' replied to ticket: '.$ticket->subject,
                    'data' => ['ticket_id' => $ticket->id],
                ]);
            }
        }

        return back()->with('status', 'Response added successfully.');
    }

    /**
     * Close ticket
     */
    public function close(SupportTicket $ticket)
    {
        if (Auth::user()->role !== 'admin' && $ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $ticket->status = 'closed';
        $ticket->save();

        return back()->with('status', 'Ticket closed successfully.');
    }

    /**
     * Reopen ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $ticket->status = 'open';
        $ticket->save();

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'Support Ticket Reopened',
                'message' => Auth::user()->name.' reopened ticket: '.$ticket->subject,
                'data' => ['ticket_id' => $ticket->id],
            ]);
        }

        return back()->with('status', 'Ticket reopened. Our team will review it shortly.');
    }
}
