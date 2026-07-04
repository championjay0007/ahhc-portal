<?php

namespace Tests\Feature;

use App\Enums\WorkerNominationStatus;
use App\Models\Participant;
use App\Models\PortalNotification;
use App\Models\User;
use App\Models\WorkerNomination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalErrorFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_to_admin_nomination_view_when_accessing_participant_nomination_show(): void
    {
        $participantUser = User::create([
            'name' => 'Participant User',
            'email' => 'participant@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-1001',
            'first_name' => 'Participant',
            'last_name' => 'Example',
            'status' => 'active',
            'phone' => '0400111222',
            'email' => 'participant@example.com',
        ]);

        $nomination = WorkerNomination::create([
            'participant_id' => $participant->id,
            'worker_full_name' => 'Julia Worker',
            'worker_email' => 'julia.worker@example.com',
            'worker_phone' => '0411222333',
            'worker_type' => 'Independent',
            'service_type' => 'Personal Care',
            'status' => WorkerNominationStatus::Submitted,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('portal.participant.nominations.show', $nomination));

        $response->assertRedirect(route('portal.admin.nominations.show', $nomination));
    }

    public function test_admin_can_open_notification_for_another_user_without_forbidden_error(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);

        $notification = PortalNotification::create([
            'user_id' => $owner->id,
            'title' => 'Test notification',
            'message' => 'A message',
            'channel' => 'in_app',
            'type' => 'info',
            'data' => [],
        ]);

        $response = $this->actingAs($admin)->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.dashboard'));
    }

    public function test_unknown_routes_redirect_authenticated_users_to_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($user)->get('/definitely-missing-route');

        $response->assertRedirect(route('portal.dashboard'));
    }
}
