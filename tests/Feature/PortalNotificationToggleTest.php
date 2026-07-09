<?php

namespace Tests\Feature;

use App\Models\Participant;
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
}
