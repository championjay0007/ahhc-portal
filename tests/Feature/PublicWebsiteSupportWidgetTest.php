<?php

namespace Tests\Feature;

use App\Models\PortalNotification;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteSupportWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_shows_support_widget(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Need help?');
        $response->assertSee('Send message');
    }

    public function test_public_support_widget_starts_support_conversation(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);

        $response = $this->postJson(route('public.support.widget.store'), [
            'name' => 'Alex Visitor',
            'email' => 'alex@example.com',
            'message' => 'I need help with my portal access.',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Support chat started. Our team will respond shortly.');
        $response->assertJsonStructure(['conversation' => ['id', 'token', 'subject', 'messages']]);
        $this->assertSame('sent', $response->json('conversation.messages.0.status'));
        $this->assertDatabaseHas('support_conversations', [
            'subject' => 'Website support request from Alex Visitor',
            'status' => 'open',
            'priority' => 'normal',
        ]);

        $conversation = SupportConversation::where('subject', 'Website support request from Alex Visitor')->first();
        $this->assertNotNull($conversation);
        $this->assertNotNull($conversation->public_token);

        $message = SupportMessage::where('support_conversation_id', $conversation->id)->first();
        $this->assertNotNull($message);
        $this->assertSame(false, (bool) $message->is_admin);
        $this->assertSame(
            "Visitor name: Alex Visitor\n\nI need help with my portal access.\n\nContact email: alex@example.com",
            str_replace("\r\n", "\n", $message->message)
        );

        $notification = PortalNotification::where('user_id', $admin->id)->latest()->first();
        $this->assertNotNull($notification);
        $this->assertSame(route('portal.admin.support.conversation.show', $conversation), $notification->data['url'] ?? null);
    }

    public function test_public_support_widget_can_send_follow_up_messages(): void
    {
        $response = $this->postJson(route('public.support.widget.store'), [
            'name' => 'Alex Visitor',
            'email' => 'alex@example.com',
            'message' => 'I need help with my portal access.',
        ]);

        $conversationId = $response->json('conversation.id');
        $token = $response->json('conversation.token');

        $followUpResponse = $this->postJson(
            route('public.support.widget.message', ['conversation' => $conversationId]).'?token='.urlencode($token),
            ['message' => 'I have one more question about the process.']
        );

        $followUpResponse->assertCreated();
        $followUpResponse->assertJsonPath('message', 'Message sent. A support agent will reply shortly.');
    }

    public function test_admin_can_send_message_in_support_conversation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = User::factory()->create(['role' => 'participant']);
        $conversation = SupportConversation::create([
            'user_id' => $participant->id,
            'admin_id' => $admin->id,
            'subject' => 'Admin chat test',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'public_token' => 'test-token',
        ]);

        $this->actingAs($admin);

        $response = $this->postJson(route('portal.admin.support.conversation.message', $conversation), [
            'message' => 'Thanks for your patience.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'user_id' => $admin->id,
            'is_admin' => true,
            'message' => 'Thanks for your patience.',
        ]);
    }

    public function test_public_support_widget_accepts_short_messages(): void
    {
        $response = $this->postJson(route('public.support.widget.store'), [
            'name' => 'Alex Visitor',
            'email' => 'alex@example.com',
            'message' => 'Hi',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Support chat started. Our team will respond shortly.');
    }

    public function test_admin_envelope_shows_unread_support_chat_count_and_links_to_chat_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = User::factory()->create(['role' => 'participant']);
        $conversation = SupportConversation::create([
            'user_id' => $participant->id,
            'admin_id' => $admin->id,
            'subject' => 'Unread chat test',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'public_token' => 'test-token-2',
        ]);

        SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => $participant->id,
            'message' => 'Hello, I still need help.',
            'is_admin' => false,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('portal.admin.support.dashboard'));

        $response->assertOk();
        $response->assertSee('Open chat list');
        $response->assertSee('Live chat');
    }
}
