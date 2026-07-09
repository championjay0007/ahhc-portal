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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportCenterController extends Controller
{
    /**
     * Admin support center dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::where('status', '!=', 'resolved')->where('status', '!=', 'closed')->count(),
            'total_conversations' => SupportConversation::count(),
            'open_conversations' => SupportConversation::where('status', '!=', 'resolved')->where('status', '!=', 'closed')->count(),
            'unread_messages' => SupportMessage::whereNull('read_at')->where('is_admin', false)->count(),
            'pending_responses' => SupportResponse::whereNull('created_at')->count(),
        ];

        $recentTickets = SupportTicket::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $activeConversations = SupportConversation::with(['user', 'messages'])
            ->where('status', '!=', 'closed')
            ->orderBy('last_message_at', 'desc')
            ->limit(8)
            ->get();

        $unreadMessages = SupportMessage::whereNull('read_at')
            ->where('is_admin', false)
            ->with(['user', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('portal.admin.support.dashboard', compact('stats', 'recentTickets', 'activeConversations', 'unreadMessages'));
    }

    /**
     * List all support tickets
     */
    public function ticketsIndex(Request $request)
    {
        $query = SupportTicket::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('portal.admin.support.tickets', compact('tickets'));
    }

    /**
     * Show support ticket details
     */
    public function ticketShow(SupportTicket $ticket)
    {
        $responses = $ticket->responses()->orderBy('created_at', 'asc')->get();

        return view('portal.admin.support.ticket-show', compact('ticket', 'responses'));
    }

    /**
     * Add response to support ticket
     */
    public function ticketResponse(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:5|max:5000',
        ]);

        $response = SupportResponse::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin' => true,
        ]);

        // Update ticket status
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in-progress']);
        }

        // Notify participant
        NotificationService::notify([
            'user_id' => $ticket->user_id,
            'title' => 'Support Ticket Response',
            'message' => 'Our support team has responded to your ticket: '.$ticket->subject,
            'data' => ['ticket_id' => $ticket->id],
        ]);

        return back()->with('status', 'Response added successfully.');
    }

    /**
     * Update ticket status
     */
    public function ticketStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in-progress,waiting,resolved,closed',
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed']) ? now() : null,
            'resolved_by' => in_array($validated['status'], ['resolved', 'closed']) ? Auth::id() : null,
        ]);

        // Notify participant
        $message = match ($validated['status']) {
            'resolved' => 'Your support ticket has been resolved.',
            'closed' => 'Your support ticket has been closed.',
            'waiting' => 'We are waiting for your response on your support ticket.',
            default => 'Your support ticket status has been updated.',
        };

        NotificationService::notify([
            'user_id' => $ticket->user_id,
            'title' => 'Support Ticket Update',
            'message' => $message,
            'data' => ['ticket_id' => $ticket->id],
        ]);

        return back()->with('status', "Ticket status updated to {$validated['status']}.");
    }

    /**
     * List all conversations
     */
    public function conversationsIndex(Request $request)
    {
        $query = SupportConversation::with(['user', 'messages']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->paginate(20);

        return view('portal.admin.support.conversations', compact('conversations'));
    }

    public function markAllConversationsRead(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $unreadCount = SupportMessage::where('is_admin', false)
            ->whereNull('read_at')
            ->count();

        SupportMessage::where('is_admin', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['status' => 'success', 'count' => 0, 'cleared' => $unreadCount]);
        }

        return back()->with('status', 'All support messages marked as read.');
    }

    /**
     * Show conversation details
     */
    public function conversationShow(SupportConversation $conversation)
    {
        $conversation->markAsRead();
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        return view('portal.admin.support.conversation-show', compact('conversation', 'messages'));
    }

    /**
     * Fetch messages for conversation (AJAX)
     */
    public function conversationMessages(SupportConversation $conversation)
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_admin' => $msg->is_admin,
                    'created_at' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    /**
     * Send message in conversation
     */
    public function conversationMessage(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin' => true,
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'admin_id' => Auth::id(),
            'status' => $conversation->status === 'open' ? 'in-progress' : $conversation->status,
        ]);

        // Notify participant
        NotificationService::notify([
            'user_id' => $conversation->user_id,
            'title' => 'New Message',
            'message' => 'You have a new message from our support team regarding: '.$conversation->subject,
            'data' => ['conversation_id' => $conversation->id],
        ]);

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'message_record' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_admin' => $message->is_admin,
                    'created_at' => $message->created_at->format('H:i'),
                ],
            ], 201);
        }

        return back()->with('status', 'Message sent successfully.');
    }

    /**
     * Update conversation status
     */
    public function conversationStatus(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in-progress,waiting,resolved,closed',
        ]);

        $conversation->update([
            'status' => $validated['status'],
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed']) ? now() : null,
        ]);

        // Notify participant
        $message = match ($validated['status']) {
            'resolved' => 'Your support conversation has been resolved.',
            'closed' => 'Your support conversation has been closed.',
            'waiting' => 'We are waiting for your response.',
            default => 'Your support conversation status has been updated.',
        };

        NotificationService::notify([
            'user_id' => $conversation->user_id,
            'title' => 'Conversation Status Update',
            'message' => $message,
            'data' => ['conversation_id' => $conversation->id],
        ]);

        return back()->with('status', "Conversation status updated to {$validated['status']}.");
    }

    /**
     * Start new conversation with a user
     */
    public function conversationCreate(Request $request)
    {
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('portal.admin.support.conversation-create', compact('users'));
    }

    /**
     * Store new conversation
     */
    public function conversationStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|min:5|max:200',
            'priority' => 'required|in:low,normal,high',
            'message' => 'required|string|min:5|max:5000',
        ]);

        DB::beginTransaction();
        try {
            $conversation = SupportConversation::create([
                'user_id' => $validated['user_id'],
                'admin_id' => Auth::id(),
                'subject' => $validated['subject'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'last_message_at' => now(),
                'public_token' => Str::uuid()->toString(),
            ]);

            SupportMessage::create([
                'support_conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
                'is_admin' => true,
            ]);

            // Notify participant
            $user = User::find($validated['user_id']);
            NotificationService::notify([
                'user_id' => $validated['user_id'],
                'title' => 'New Support Conversation',
                'message' => 'Our support team has started a conversation with you regarding: '.$validated['subject'],
                'data' => [
                    'conversation_id' => $conversation->id,
                    'url' => route('portal.support.conversations.show', $conversation),
                ],
            ]);

            DB::commit();

            return redirect()->route('portal.admin.support.conversation.show', $conversation)
                ->with('status', 'Conversation started successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to start conversation: '.$e->getMessage()]);
        }
    }
}
