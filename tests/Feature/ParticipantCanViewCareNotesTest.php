<?php

namespace Tests\Feature;

use App\Models\CareNote;
use App\Models\Participant;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantCanViewCareNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_view_their_care_notes()
    {
        $participant = Participant::factory()->create();
        $user = $participant->user;

        $worker = Worker::factory()->create();

        CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'shift_date' => now()->toDateString(),
            'tasks_completed' => 'Checked medication and provided support',
            'care_summary' => 'Checked medication and provided support',
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.participant.care_notes.index'));

        $response->assertStatus(200);
        $response->assertSee('Care note history');
        $response->assertSee('Checked medication and provided support');
    }
}
