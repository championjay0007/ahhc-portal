<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_retrieve_push_public_key(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => bcrypt('Password123!'),
            'password_changed_at' => now(),
        ]);

        config(['push.vapid.public_key' => 'test_public_key']);

        $response = $this->actingAs($user)->getJson(route('portal.push.public_key'));

        $response->assertOk()
            ->assertJson(['publicKey' => 'test_public_key']);
    }

    public function test_authenticated_user_can_store_push_subscription(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => bcrypt('Password123!'),
            'password_changed_at' => now(),
        ]);

        $payload = [
            'endpoint' => 'https://example.com/push/abcdef',
            'keys' => [
                'p256dh' => 'test_p256dh',
                'auth' => 'test_auth',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $response = $this->actingAs($user)->postJson(route('portal.push.subscription.store'), $payload);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $payload['endpoint'],
            'public_key' => $payload['keys']['p256dh'],
            'auth_token' => $payload['keys']['auth'],
            'content_encoding' => 'aes128gcm',
        ]);
    }

    public function test_authenticated_user_can_delete_push_subscription(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => bcrypt('Password123!'),
            'password_changed_at' => now(),
        ]);

        $endpoint = 'https://example.com/push/abcdef';

        $this->postJson(route('portal.push.subscription.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'test_p256dh',
                'auth' => 'test_auth',
            ],
            'contentEncoding' => 'aes128gcm',
        ]);

        $response = $this->actingAs($user)->deleteJson(route('portal.push.subscription.destroy'), [
            'endpoint' => $endpoint,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'deleted']);

        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $endpoint,
        ]);
    }
}
