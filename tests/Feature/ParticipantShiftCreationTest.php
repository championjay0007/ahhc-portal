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

        $response->assertRedirect(route('portal.participant.services'));
        $this->assertDatabaseHas('shifts', [
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'service_type' => 'Personal Care',
            'status' => Shift::STATUS_SCHEDULED,
        ]);
    }
}
