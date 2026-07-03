<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportConversationController extends Controller
{
    public function index()
    {
        $conversations = SupportConversation::withCount(['messages as unread_messages_count' => function ($query) {
            $query->where('is_admin', true)->whereNull('read_at');
        }])
            ->where('user_id', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->paginate(12);

        return view('portal.participant.support.conversations', compact('conversations'));
    }

    public function show(SupportConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $messages = $conversation->messages()->with('user')->orderBy('created_at', 'asc')->get();
        $conversation->messages()->where('is_admin', true)->whereNull('read_at')->update(['read_at' => now()]);

        return view('portal.participant.support.conversation-show', compact('conversation', 'messages'));
    }

    public function messages(SupportConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $messages = $conversation->messages()->with('user')->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'text' => $message->message,
                'is_admin' => $message->is_admin,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'author' => $message->user ? $message->user->name : 'Support',
            ]),
        ]);
    }

    public function sendMessage(Request $request, SupportConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'waiting',
        ]);

        $recipientAdmins = User::where('role', 'admin');
        if ($conversation->admin_id) {
            $recipientAdmins->where('id', $conversation->admin_id);
        }

        foreach ($recipientAdmins->get() as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'New Support Conversation Message',
                'message' => Auth::user()->name.' sent a new message in "'.$conversation->subject.'"',
                'data' => ['conversation_id' => $conversation->id],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 201);
        }

        return back()->with('status', 'Message sent successfully.');
    }
}
