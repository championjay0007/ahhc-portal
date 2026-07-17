<?php

namespace Tests\Feature;

use App\Models\CareNote;
use App\Models\Participant;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantCanOpenCareNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_open_individual_care_note()
    {
        $participant = Participant::factory()->create();
        $user = $participant->user;
        $worker = Worker::factory()->create();

        $note = CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'shift_date' => now()->toDateString(),
            'tasks_completed' => 'Assisted with mobility',
            'care_summary' => 'Assisted with mobility',
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.participant.care_notes.show', $note));

        $response->assertStatus(200);
        $response->assertSee('Care Note');
        $response->assertSee('Assisted with mobility');
    }
}
