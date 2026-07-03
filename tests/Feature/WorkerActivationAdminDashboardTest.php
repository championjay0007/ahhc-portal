<?php

namespace Tests\Feature;

use App\Enums\WorkerNominationStatus;
use App\Models\Participant;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerNomination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkerActivationAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_activation_updates_worker_status_user_status_and_admin_dashboard_counts(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

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

        $workerUser = User::create([
            'name' => 'Pending Worker',
            'email' => 'worker@example.com',
            'role' => 'worker',
            'status' => 'inactive',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $worker = Worker::create([
            'first_name' => 'Pending',
            'last_name' => 'Worker',
            'email' => 'worker@example.com',
            'phone' => '0411000111',
            'role_type' => 'worker',
            'status' => 'pending',
            'worker_number' => 'W-TEST001',
        ]);

        $nomination = WorkerNomination::create([
            'participant_id' => $participant->id,
            'worker_full_name' => 'Pending Worker',
            'worker_email' => 'worker@example.com',
            'worker_phone' => '0411000111',
            'worker_type' => 'Independent',
            'service_type' => 'Personal Care',
            'status' => WorkerNominationStatus::WorkerInvited,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('portal.admin.nominations.activate', $nomination));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Worker activated and linked to nomination.');

        $worker->refresh();
        $workerUser->refresh();
        $nomination->refresh();

        $this->assertSame('active', $worker->status);
        $this->assertSame($workerUser->id, $worker->user_id);
        $this->assertSame('active', $workerUser->status);
        $this->assertSame('worker', $workerUser->role);
        $this->assertSame(WorkerNominationStatus::Active, $nomination->status);

        $dashboardResponse = $this->actingAs($admin)->get(route('portal.admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas('workersCount', 1);
        $dashboardResponse->assertViewHas('workersPending', 0);
    }

    public function test_admin_dashboard_counts_pending_onboarding_workers(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        Worker::create([
            'first_name' => 'Pending',
            'last_name' => 'Onboarding',
            'email' => 'pending.worker@example.com',
            'phone' => '0411222333',
            'role_type' => 'worker',
            'status' => 'pending',
            'worker_number' => 'W-TEST002',
        ]);

        $response = $this->actingAs($admin)->get(route('portal.admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('workersCount', 1);
        $response->assertViewHas('workersPending', 1);
    }
}
