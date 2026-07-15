<?php

namespace Tests\Feature;

use App\Models\CareNote;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PreApprovalRequest;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_pre_approval_invoice_and_document_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'request_number' => 'PA-'.uniqid(),
            'status' => PreApprovalRequest::STATUS_SUBMITTED,
            'service_type' => 'Support',
            'service_category' => 'Personal Care',
            'purpose' => 'Need support',
            'requested_amount_cents' => 10000,
            'submitted_at' => now(),
        ]);

        $invoice = Invoice::create([
            'participant_id' => $participant->id,
            'pre_approval_id' => $preApproval->id,
            'invoice_number' => 'INV-'.uniqid(),
            'status' => 'submitted',
            'amount_cents' => 10000,
            'invoice_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $this->delete(route('portal.admin.pre_approvals.destroy', $preApproval))
            ->assertRedirect(route('portal.admin.pre_approvals'));

        $invoice = Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-'.uniqid(),
            'status' => 'submitted',
            'amount_cents' => 10000,
            'invoice_date' => now()->toDateString(),
        ]);

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => 'care_plan',
            'title' => 'Test Document',
            'storage_disk' => 'local',
            'path' => 'documents/test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'status' => 'uploaded',
        ]);

        $this->delete(route('portal.admin.invoices.destroy', $invoice))
            ->assertRedirect(route('portal.admin.invoices'));

        $this->delete(route('portal.admin.documents.destroy', $document))
            ->assertRedirect(route('portal.admin.documents'));

        $this->assertDatabaseMissing('pre_approval_requests', ['id' => $preApproval->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_admin_can_delete_care_note_incident_and_complaint_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $worker = Worker::factory()->create();

        $careNote = CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'visit_date' => now()->toDateString(),
            'care_summary' => 'Test summary',
            'service_type' => 'Personal Care',
            'description' => 'Routine check',
        ]);

        $incident = Incident::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'incident_type' => 'fall',
            'severity' => 'high',
            'description' => 'Test incident',
            'occurred_at' => now(),
        ]);

        $complaint = Complaint::create([
            'participant_id' => $participant->id,
            'category' => 'service',
            'priority' => 'high',
            'description' => 'Test complaint',
        ]);

        $this->actingAs($admin);

        $this->delete(route('portal.admin.care_notes.destroy', $careNote))
            ->assertRedirect(route('portal.admin.care_notes'));

        $this->delete(route('portal.admin.incidents.destroy', $incident))
            ->assertRedirect(route('portal.admin.incidents'));

        $this->delete(route('portal.admin.complaints.destroy', $complaint))
            ->assertRedirect(route('portal.admin.complaints'));

        $this->assertDatabaseMissing('care_notes', ['id' => $careNote->id]);
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
        $this->assertDatabaseMissing('complaints', ['id' => $complaint->id]);
    }

    public function test_admin_can_see_delete_button_on_gallery_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => 'care_plan',
            'title' => 'Gallery Test Document',
            'storage_disk' => 'local',
            'path' => 'documents/test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'uploaded_by_id' => $admin->id,
            'status' => 'uploaded',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('portal.gallery'));

        $response->assertOk()
            ->assertSee('Gallery Test Document')
            ->assertSee('Delete')
            ->assertSee(route('portal.gallery.destroy', $document));
    }
}
