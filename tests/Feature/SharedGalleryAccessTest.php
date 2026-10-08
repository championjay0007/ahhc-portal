<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Participant;
use App\Models\ParticipantAccountDelegation;
use App\Models\ParticipantAssignment;
use App\Models\SignatureRequest;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SharedGalleryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_only_sees_and_accesses_their_own_documents(): void
    {
        Storage::fake('local');

        $participant = Participant::factory()->create();
        $otherParticipant = Participant::factory()->create();
        $ownDocument = $this->createDocument($participant, $participant->user_id);
        $otherDocument = $this->createDocument($otherParticipant, $otherParticipant->user_id);
        $assignedDocument = $this->createDocument($otherParticipant, $otherParticipant->user_id);
        $this->assignDocument($assignedDocument, $participant->user_id);

        Storage::disk('local')->put($ownDocument->path, 'own document');
        Storage::disk('local')->put($otherDocument->path, 'other document');
        Storage::disk('local')->put($assignedDocument->path, 'assigned document');

        $this->actingAs($participant->user)
            ->get(route('portal.gallery'))
            ->assertOk()
            ->assertSee($ownDocument->title)
            ->assertSee($assignedDocument->title)
            ->assertDontSee($otherDocument->title);

        $this->get(route('portal.gallery.preview', $otherDocument))->assertNotFound();
        $this->get(route('portal.gallery.download', $otherDocument))->assertNotFound();
        $this->delete(route('portal.gallery.destroy', $otherDocument))->assertNotFound();
        $this->get(route('portal.gallery.download', $assignedDocument))->assertOk();

        $this->assertDatabaseHas('documents', ['id' => $otherDocument->id]);
    }

    public function test_worker_only_sees_their_documents_and_their_uploads_for_active_assignments(): void
    {
        $workerUser = User::factory()->create(['role' => 'worker']);
        $worker = Worker::factory()->create(['user_id' => $workerUser->id]);
        $assignedParticipant = Participant::factory()->create();
        $unassignedParticipant = Participant::factory()->create();
        ParticipantAssignment::create([
            'participant_id' => $assignedParticipant->id,
            'worker_id' => $worker->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $workerDocument = $this->createDocument($worker, $workerUser->id);
        $workerUpload = $this->createDocument($assignedParticipant, $workerUser->id);
        $otherParticipantDocument = $this->createDocument($assignedParticipant, $assignedParticipant->user_id);
        $unassignedUpload = $this->createDocument($unassignedParticipant, $workerUser->id);
        $assignedDocument = $this->createDocument($unassignedParticipant, $unassignedParticipant->user_id);
        $this->assignDocument($assignedDocument, $workerUser->id);

        $this->actingAs($workerUser)
            ->get(route('portal.gallery'))
            ->assertOk()
            ->assertSee($workerDocument->title)
            ->assertSee($workerUpload->title)
            ->assertSee($assignedDocument->title)
            ->assertDontSee($otherParticipantDocument->title)
            ->assertDontSee($unassignedUpload->title);
    }

    public function test_manager_sees_documents_for_the_selected_participant_account(): void
    {
        Storage::fake('local');

        $participant = Participant::factory()->create();
        $otherParticipant = Participant::factory()->create();
        $manager = User::factory()->create(['role' => 'manager']);
        ParticipantAccountDelegation::create([
            'participant_id' => $participant->id,
            'manager_user_id' => $manager->id,
            'invited_email' => $manager->email,
            'accepted_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $participantDocument = $this->createDocument($participant, $participant->user_id);
        $assignedDocument = $this->createDocument($otherParticipant, $otherParticipant->user_id);
        $this->assignDocument($assignedDocument, $participant->user_id);
        $managerAssignedDocument = $this->createDocument($otherParticipant, $otherParticipant->user_id);
        $this->assignDocument($managerAssignedDocument, $manager->id);
        $unrelatedDocument = $this->createDocument($otherParticipant, $otherParticipant->user_id);
        Storage::disk('local')->put($assignedDocument->path, 'assigned document');

        $this->actingAs($manager)
            ->withSession(['participant_account_user_id' => $participant->user_id])
            ->get(route('portal.gallery'))
            ->assertOk()
            ->assertSee($participantDocument->title)
            ->assertSee($assignedDocument->title)
            ->assertSee($managerAssignedDocument->title)
            ->assertDontSee($unrelatedDocument->title);

        $this->actingAs($manager)
            ->withSession(['participant_account_user_id' => $participant->user_id])
            ->get(route('portal.gallery.preview', $assignedDocument))
            ->assertOk();

        $this->actingAs($manager)
            ->withSession(['participant_account_user_id' => $participant->user_id])
            ->get(route('portal.gallery.preview', $unrelatedDocument))
            ->assertNotFound();
    }

    private function assignDocument(Document $document, int $userId): void
    {
        $assignedBy = User::factory()->create(['role' => 'admin']);

        SignatureRequest::create([
            'document_id' => $document->id,
            'assigned_user_id' => $userId,
            'assigned_by' => $assignedBy->id,
            'status' => SignatureRequest::STATUS_PENDING,
            'assigned_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    private function createDocument(Participant|Worker $owner, int $uploadedById): Document
    {
        return Document::create([
            'owner_type' => $owner::class,
            'owner_id' => $owner->id,
            'document_type' => 'care_plan',
            'title' => 'Gallery document '.uniqid(),
            'storage_disk' => 'local',
            'path' => 'documents/gallery-'.uniqid().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'uploaded_by_id' => $uploadedById,
            'status' => 'uploaded',
        ]);
    }
}
