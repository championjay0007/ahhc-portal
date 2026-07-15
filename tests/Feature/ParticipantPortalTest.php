<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PortalSetting;
use App\Models\PreApprovalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_upload_a_document(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Participant Upload',
            'email' => 'participant-upload@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1001',
            'first_name' => 'Participant',
            'last_name' => 'Upload',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('portal.participant.documents.store'), [
            'title' => 'Care plan',
            'document_type' => 'care_plan',
            'file' => UploadedFile::fake()->create('care-plan.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('portal.participant.documents.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('success', 'Your document has been uploaded successfully. Our admin team will review it shortly. Please wait for their response.');

        $this->assertDatabaseHas('documents', [
            'title' => 'Care plan',
            'document_type' => 'care_plan',
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
        ]);

        $document = Document::first();
        $this->assertNotNull($document);
        $this->assertDatabaseHas('document_versions', [
            'document_id' => $document->id,
            'version_number' => 1,
        ]);

        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_participant_can_upload_an_excel_document(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Participant Excel',
            'email' => 'participant-excel@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1002',
            'first_name' => 'Participant',
            'last_name' => 'Excel',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('portal.participant.documents.store'), [
            'title' => 'Budget worksheet',
            'document_type' => 'budget_plan',
            'file' => UploadedFile::fake()->create('budget.xlsx', 80, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertDatabaseHas('documents', [
            'title' => 'Budget worksheet',
            'document_type' => 'budget_plan',
            'uploaded_by_id' => $user->id,
        ]);
    }

    public function test_document_form_shows_inline_validation_feedback(): void
    {
        $user = User::create([
            'name' => 'Participant Validation',
            'email' => 'participant-validation@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1007',
            'first_name' => 'Participant',
            'last_name' => 'Validation',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->from(route('portal.participant.documents.create'))
            ->followingRedirects()
            ->post(route('portal.participant.documents.store'), [
                'title' => '',
                'document_type' => '',
                'file' => null,
            ]);

        $response->assertStatus(200);
        $response->assertSee('The title field is required.');
        $response->assertSee('The document type field is required.');
        $response->assertSee('The file field is required.');
        $response->assertSee('invalid-feedback');
    }

    public function test_participant_cannot_download_document_without_stored_file_path(): void
    {
        $user = User::create([
            'name' => 'Participant No File',
            'email' => 'participant-nofile@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1008',
            'first_name' => 'Participant',
            'last_name' => 'NoFile',
            'status' => 'active',
        ]);

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => 'monthly_care_management_checklist',
            'title' => 'Checklist Document',
            'storage_disk' => 'local',
            'path' => '',
            'mime_type' => '',
            'size_bytes' => 0,
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'metadata' => ['items' => ['item1', 'item2'], 'submitted_at' => now()->toDateTimeString()],
            'is_sensitive' => false,
        ]);

        $response = $this->actingAs($user)->get(route('portal.participant.documents.download', $document));

        $response->assertRedirect(route('portal.participant.documents.show', $document));
        $response->assertSessionHas('error', 'This document is not available for download.');
    }

    public function test_participant_can_download_an_uploaded_document(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Participant Download',
            'email' => 'participant-download@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1006',
            'first_name' => 'Participant',
            'last_name' => 'Download',
            'status' => 'active',
        ]);

        $uploadedFile = UploadedFile::fake()->create('care-plan.pdf', 120, 'application/pdf');
        $path = $uploadedFile->store('documents', 'local');

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => 'care_plan',
            'title' => 'Care plan',
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size_bytes' => $uploadedFile->getSize(),
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'is_sensitive' => true,
        ]);

        $response = $this->actingAs($user)->get(route('portal.participant.documents.download', $document));

        $response->assertDownload('Care plan');
    }

    public function test_participant_can_sign_an_uploaded_document(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Participant Sign',
            'email' => 'participant-sign@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1010',
            'first_name' => 'Participant',
            'last_name' => 'Sign',
            'status' => 'active',
        ]);

        $uploadedFile = UploadedFile::fake()->create('consent.pdf', 120, 'application/pdf');
        $path = $uploadedFile->store('documents', 'local');

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => 'consent_form',
            'title' => 'Consent Form',
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size_bytes' => $uploadedFile->getSize(),
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'is_sensitive' => true,
        ]);

        $response = $this->actingAs($user)->post(route('portal.participant.documents.sign', $document), [
            'confirm_signature' => '1',
        ]);

        $response->assertRedirect(route('portal.participant.documents.show', $document));
        $response->assertSessionHas('status', 'Document signed successfully.');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'signed',
        ]);

        $this->assertDatabaseHas('document_signatures', [
            'document_id' => $document->id,
            'signed_by_id' => $user->id,
            'signature_method' => 'electronic',
        ]);
    }

    public function test_participant_can_submit_a_pre_approval_request(): void
    {
        $user = User::create([
            'name' => 'Participant Preapproval',
            'email' => 'participant-preapproval@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1002',
            'first_name' => 'Participant',
            'last_name' => 'Preapproval',
            'status' => 'active',
        ]);

        $quoteFile = UploadedFile::fake()->create('quote.pdf', 120, 'application/pdf');

        $response = $this->actingAs($user)->post(route('portal.participant.pre_approvals.store'), [
            'service_type' => 'transport',
            'service_category' => 'Transport',
            'purpose' => 'Medical appointment transport support',
            'description' => 'Medical appointment transport support',
            'requested_amount' => 25.00,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'quote' => $quoteFile,
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('status', 'Pre-approval request submitted successfully.');

        $this->assertDatabaseHas('pre_approval_requests', [
            'participant_id' => $participant->id,
            'service_type' => 'transport',
            'purpose' => 'Medical appointment transport support',
            'requested_amount_cents' => 2500,
            'status' => 'submitted',
        ]);
    }

    public function test_participant_can_respond_to_info_request(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Participant Info Request',
            'email' => 'participant-info@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1004',
            'first_name' => 'Participant',
            'last_name' => 'InfoRequest',
            'status' => 'active',
        ]);

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'support_person_id' => $participant->assigned_support_person_id,
            'request_number' => 'PA-TEST-INFO',
            'service_type' => 'home_care',
            'service_category' => 'Home care',
            'purpose' => 'Cleaning support',
            'description' => 'Cleaning support for the home',
            'requested_amount_cents' => 15000,
            'status' => 'info_requested',
            'submitted_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->post(route('portal.participant.pre_approvals.update', $preApproval), [
            'participant_note' => 'Please see the attached additional documentation.',
            'quote' => UploadedFile::fake()->create('extra-quote.pdf', 80, 'application/pdf'),
            'description' => 'Updated cleaning support description',
        ]);

        $response->assertSessionHas('status', 'Pre-approval request updated successfully.');
        $this->assertDatabaseHas('pre_approval_requests', [
            'id' => $preApproval->id,
            'status' => 'submitted',
            'description' => 'Updated cleaning support description',
        ]);
        $this->assertDatabaseHas('pre_approval_comments', [
            'pre_approval_request_id' => $preApproval->id,
            'comment_type' => 'participant_response',
            'message' => 'Please see the attached additional documentation.',
        ]);
    }

    public function test_participant_can_submit_an_invoice(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Participant Invoice',
            'email' => 'participant-invoice@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1003',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        $invoiceFile = UploadedFile::fake()->create('invoice.pdf', 120, 'application/pdf');

        $response = $this->actingAs($user)->post(route('portal.participant.invoices.store'), [
            'invoice_number' => 'INV-1001',
            'invoice_date' => '2026-05-25',
            'service_date' => '2026-05-24',
            'due_date' => '2026-06-10',
            'amount' => 110.00,
            'notes' => 'Support invoice for May care services',
            'attachment' => $invoiceFile,
        ]);

        $response->assertRedirect(route('portal.participant.invoices.index'));
        $response->assertSessionHas('status', 'Invoice created.');

        $this->assertDatabaseHas('invoices', [
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-1001',
            'status' => 'submitted',
            'amount_cents' => 11000,
        ]);
    }

    public function test_participant_can_submit_a_complaint(): void
    {
        $user = User::create([
            'name' => 'Participant Complaint',
            'email' => 'participant-complaint@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1004',
            'first_name' => 'Participant',
            'last_name' => 'Complaint',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('portal.participant.complaints.store'), [
            'category' => 'service_quality',
            'priority' => 'high',
            'description' => 'The service visit was late and did not include the agreed support.',
            'notes' => 'Please review and follow up.',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('status', 'Complaint submitted successfully.');

        $this->assertDatabaseHas('complaints', [
            'participant_id' => $participant->id,
            'category' => 'service_quality',
            'priority' => 'high',
            'description' => 'The service visit was late and did not include the agreed support.',
            'status' => 'open',
            'submitted_by_id' => $user->id,
        ]);
    }

    public function test_participant_can_view_budget_usage(): void
    {
        $user = User::create([
            'name' => 'Participant Budget',
            'email' => 'participant-budget@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1005',
            'first_name' => 'Participant',
            'last_name' => 'Budget',
            'status' => 'active',
        ]);

        Budget::updateOrCreate([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter(),
            'quarter_end_date' => now()->endOfQuarter(),
        ], [
            'opening_balance_cents' => 50000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('portal.participant.budget'));

        $response->assertStatus(200);
        $response->assertSeeText('My Budget');
    }

    public function test_participant_dashboard_shows_committed_from_invoice_override(): void
    {
        $user = User::create([
            'name' => 'Participant Invoice Override',
            'email' => 'participant-invoice-override@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1006',
            'first_name' => 'Participant',
            'last_name' => 'Override',
            'status' => 'active',
        ]);

        Budget::updateOrCreate([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter(),
            'quarter_end_date' => now()->endOfQuarter(),
        ], [
            'opening_balance_cents' => 100000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        PortalSetting::create([
            'key' => 'invoice_budget_mode',
            'value' => 'committed_amount',
        ]);

        Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-OVR-1001',
            'status' => 'submitted',
            'amount_cents' => 50000,
            'committed_amount_cents' => 25000,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeeks(1)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('$250.00');
        $response->assertSeeText('Committed');
    }

    public function test_preapproval_mode_ignores_invoice_committed_override_without_preapproval(): void
    {
        PortalSetting::create([
            'key' => 'invoice_budget_mode',
            'value' => 'preapproval_amount',
        ]);

        $user = User::create([
            'name' => 'Participant Mode Preserve',
            'email' => 'participant-mode-preserve@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-1007',
            'first_name' => 'Participant',
            'last_name' => 'ModePreserve',
            'status' => 'active',
        ]);

        Budget::updateOrCreate([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter(),
            'quarter_end_date' => now()->endOfQuarter(),
        ], [
            'opening_balance_cents' => 100000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-MODE-1001',
            'status' => 'submitted',
            'amount_cents' => 50000,
            'committed_amount_cents' => 25000,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeeks(1)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('$250.00');
        $response->assertSee('$0.00');
    }
}
