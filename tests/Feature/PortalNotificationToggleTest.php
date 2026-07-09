<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Participant;
use App\Models\PortalNotification;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalNotificationToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_notification_and_message_toggles_render_with_button_types(): void
    {
        $user = User::create([
            'name' => 'Notification Toggle User',
            'email' => 'notification-toggle-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-'.$user->id,
            'first_name' => 'Notification',
            'last_name' => 'Toggle',
            'status' => 'active',
            'phone' => '0400000000',
            'email' => $user->email,
            'consent_to_share' => false,
            'budget_limit_cents' => 10000,
            'current_budget_used_cents' => 0,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="portalNotificationToggle"', false);
        $response->assertSee('id="portalMessageToggle"', false);
        $response->assertSee('id="portalNotificationDropdown"', false);
        $response->assertSee('id="portalMessageDropdown"', false);
        $response->assertSee('type="button"', false);
    }

    public function test_unread_messages_can_be_cleared_via_mark_all_read_route(): void
    {
        $user = User::create([
            'name' => 'Unread Message User',
            'email' => 'unread-message-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $otherUser = User::create([
            'name' => 'Sender User',
            'email' => 'sender-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        Message::create([
            'sender_id' => $otherUser->id,
            'recipient_id' => $user->id,
            'subject' => 'Unread message',
            'body' => 'Please review',
            'read_at' => null,
        ]);

        PortalNotification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'data' => ['title' => 'Test', 'message' => 'Hello'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('portal.messages.mark_all_read'));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('messages', ['recipient_id' => $user->id, 'read_at' => null]);
        $this->assertDatabaseHas('messages', ['recipient_id' => $user->id]);
    }

    public function test_admin_envelope_count_is_distinct_unread_support_conversations(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-user@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $visitor = User::create([
            'name' => 'Visitor User',
            'email' => 'visitor-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $conversationA = SupportConversation::create([
            'user_id' => $visitor->id,
            'admin_id' => $admin->id,
            'subject' => 'First conversation',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $conversationB = SupportConversation::create([
            'user_id' => $visitor->id,
            'admin_id' => $admin->id,
            'subject' => 'Second conversation',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'support_conversation_id' => $conversationA->id,
            'user_id' => $visitor->id,
            'message' => 'New request A',
            'is_admin' => false,
            'read_at' => null,
        ]);

        SupportMessage::create([
            'support_conversation_id' => $conversationB->id,
            'user_id' => $visitor->id,
            'message' => 'New request B',
            'is_admin' => false,
            'read_at' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('portal.admin.support.conversations'));

        $response->assertStatus(200);
        $response->assertViewHas('unreadSupportConversationCount', 2);
    }
}
