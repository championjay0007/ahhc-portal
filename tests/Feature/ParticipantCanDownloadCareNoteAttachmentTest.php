<?php

namespace Tests\Feature;

use App\Models\CareNote;
use App\Models\Participant;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantCanDownloadCareNoteAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_download_their_care_note_attachment()
    {
        Storage::fake('local');

        $participant = Participant::factory()->create();
        $user = $participant->user;
        $worker = Worker::factory()->create();

        $path = 'care_notes/test-attachment.pdf';
        Storage::disk('local')->put($path, 'dummy-content');

        $note = CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'shift_date' => now()->toDateString(),
            'tasks_completed' => 'Assisted with hygiene',
            'care_summary' => 'Assisted with hygiene',
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_id' => $user->id,
            'attachment_path' => $path,
        ]);

        $response = $this->actingAs($user)->get(route('portal.participant.care_notes.attachment.download', $note));

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', (string) $disposition);
    }
}
