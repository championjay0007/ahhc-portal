<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Shift;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantShiftCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_create_a_shift_for_an_assigned_worker(): void
    {
        $participantUser = User::create([
            'name' => 'Participant User',
            'email' => 'participant-shift@example.com',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-2001',
            'first_name' => 'Alice',
            'last_name' => 'Example',
            'email' => 'alice@example.com',
            'phone' => '0400000001',
            'status' => 'active',
        ]);

        $worker = Worker::create([
            'user_id' => null,
            'worker_number' => 'W-2001',
            'first_name' => 'Bob',
            'last_name' => 'Worker',
            'email' => 'bob@example.com',
            'phone' => '0400000002',
            'status' => 'approved',
            'role_type' => 'Independent',
        ]);

        $response = $this->actingAs($participantUser)
            ->followingRedirects()
            ->post(route('portal.participant.services.shifts.create'), [
                'worker_id' => $worker->id,
                'service_type' => 'Personal Care',
                'service_category' => 'Domestic Assistance',
                'shift_date' => '2030-01-15',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'location' => 'Home',
                'notes' => 'Created by participant',
                'status' => 'scheduled',
            ]);

        $response->assertSee('Shift created successfully.');
        $response->assertSee('Personal Care');
        $response->assertSee('09:00');
        $response->assertSee('15 Jan 2030');

        $this->assertDatabaseHas('shifts', [
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'service_type' => 'Personal Care',
            'status' => Shift::STATUS_SCHEDULED,
        ]);
    }

    public function test_created_shift_is_visible_to_worker_on_my_shifts_page(): void
    {
        $participantUser = User::create([
            'name' => 'Participant User',
            'email' => 'participant-shift-2@example.com',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-2002',
            'first_name' => 'Alice',
            'last_name' => 'Example',
            'email' => 'alice2@example.com',
            'phone' => '0400000003',
            'status' => 'active',
        ]);

        $workerUser = User::create([
            'name' => 'Worker User',
            'email' => 'worker-shift@example.com',
            'role' => 'worker',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $worker = Worker::create([
            'user_id' => $workerUser->id,
            'worker_number' => 'W-2002',
            'first_name' => 'Bob',
            'last_name' => 'Worker',
            'email' => 'bob2@example.com',
            'phone' => '0400000004',
            'status' => 'approved',
            'role_type' => 'Independent',
            'onboarding_stage' => 6,
        ]);

        Shift::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'service_type' => 'Personal Care',
            'service_category' => 'Domestic Assistance',
            'shift_date' => '2030-01-16',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'location' => 'Home',
            'notes' => 'Visible to worker',
            'status' => Shift::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($workerUser)
            ->get(route('portal.worker.shifts'));

        $response->assertStatus(200);
        $response->assertSee('16 Jan 2030');
        $response->assertSee('Alice Example');
        $response->assertSee('Personal Care');
    }
}
