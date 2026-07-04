<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_send_messages_page_renders_without_blade_template_errors(): void
    {
        $admin = User::create([
            'name' => 'Admin Message Sender',
            'email' => 'admin-message-sender@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('portal.admin.messages.send.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_any_message_detail(): void
    {
        $admin = User::create([
            'name' => 'Admin Viewer',
            'email' => 'admin-viewer@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participant = User::create([
            'name' => 'Participant Viewer',
            'email' => 'participant-viewer@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $message = Message::create([
            'sender_id' => $participant->id,
            'recipient_id' => $admin->id,
            'subject' => 'Message access test',
            'body' => 'This message should be viewable by the admin.',
        ]);

        $response = $this->actingAs($admin)->get(route('portal.messages.show', $message));

        $response->assertStatus(200);
        $response->assertSee('Message access test');
    }

    public function test_notification_opens_message_conversation_page(): void
    {
        $sender = User::create([
            'name' => 'Sender User',
            'email' => 'sender-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $recipient = User::create([
            'name' => 'Recipient User',
            'email' => 'recipient-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Conversation notification test',
            'body' => 'Please reply in-app from this conversation view.',
        ]);

        $notification = PortalNotification::create([
            'user_id' => $recipient->id,
            'title' => 'New message',
            'message' => 'You have a new message.',
            'type' => 'info',
            'channel' => 'in_app',
            'data' => ['message_id' => $message->id],
        ]);

        $response = $this->actingAs($recipient)->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.messages.conversation.from_message', ['message' => $message->id]));
    }
}
