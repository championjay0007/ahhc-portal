<?php

namespace Tests\Feature;

use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_owner_can_open_notification_without_forbidden_error(): void
    {
        $user = User::factory()->create();
        $notification = PortalNotification::create([
            'user_id' => $user->id,
            'recipient_id' => $user->id,
            'title' => 'Test notification',
            'message' => 'Notification body',
            'type' => 'test',
            'channel' => 'in_app',
            'data' => ['url' => 'https://example.com'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portal.notifications.show', $notification));

        $response->assertRedirect('https://example.com');
    }

    public function test_notification_non_owner_is_redirected_instead_of_forbidden(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $notification = PortalNotification::create([
            'user_id' => $owner->id,
            'recipient_id' => $owner->id,
            'title' => 'Test notification',
            'message' => 'Notification body',
            'type' => 'test',
            'channel' => 'in_app',
            'data' => ['url' => 'https://example.com'],
        ]);

        $this->actingAs($viewer);

        $response = $this->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.dashboard'));
    }
}
