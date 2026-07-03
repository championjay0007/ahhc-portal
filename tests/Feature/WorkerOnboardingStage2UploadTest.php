<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerServiceApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkerOnboardingStage2UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_upload_stage2_compliance_documents_by_named_fields(): void
    {
        Storage::fake('private');

        $worker = Worker::create([
            'worker_number' => 'W-1001',
            'first_name' => 'Test',
            'last_name' => 'Worker',
            'phone' => '0400111222',
            'email' => 'test.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 2,
            'onboarding_token' => 'test-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $response = $this->post(route('worker.onboarding.stage2.submit', ['token' => $worker->onboarding_token]), [
            'documents' => [
                'abn_verification' => UploadedFile::fake()->create('abn.pdf', 100, 'application/pdf'),
                'police_check' => UploadedFile::fake()->create('policecheck.pdf', 100, 'application/pdf'),
                'ndis_worker_screening' => UploadedFile::fake()->create('screening.pdf', 100, 'application/pdf'),
                'insurance' => UploadedFile::fake()->create('insurance.pdf', 100, 'application/pdf'),
                'qualification' => UploadedFile::fake()->create('qualification.pdf', 100, 'application/pdf'),
                'first_aid_certificate' => UploadedFile::fake()->create('firstaid.pdf', 100, 'application/pdf'),
                'cpr_certificate' => UploadedFile::fake()->create('cpr.pdf', 100, 'application/pdf'),
                'registration' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('worker.onboarding.show', ['token' => $worker->onboarding_token]));
        $response->assertSessionHas('success', 'Compliance documents submitted successfully. AHHC will review them and notify you when complete.');

        $worker->refresh();

        $this->assertNotNull($worker->stage_2_submitted_at);
        $this->assertDatabaseHas('worker_compliance_documents', [
            'worker_id' => $worker->id,
            'document_type' => 'ABN Verification',
            'status' => 'submitted',
        ]);
        $this->assertDatabaseHas('worker_compliance_documents', [
            'worker_id' => $worker->id,
            'document_type' => 'Police Check',
            'status' => 'submitted',
        ]);
        $this->assertDatabaseHas('worker_compliance_documents', [
            'worker_id' => $worker->id,
            'document_type' => 'NDIS Worker Screening',
            'status' => 'submitted',
        ]);

        $document = WorkerComplianceDocument::where('worker_id', $worker->id)
            ->where('document_type', 'ABN Verification')
            ->first();

        $this->assertNotNull($document);
        Storage::disk('private')->assertExists($document->document_path);
    }

    public function test_worker_is_shown_stage3_review_after_submitting_stage2_documents(): void
    {
        Storage::fake('private');

        $worker = Worker::create([
            'worker_number' => 'W-1004',
            'first_name' => 'Review',
            'last_name' => 'Worker',
            'phone' => '0400111225',
            'email' => 'review.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 2,
            'onboarding_token' => 'review-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $response = $this->followingRedirects()->post(route('worker.onboarding.stage2.submit', ['token' => $worker->onboarding_token]), [
            'documents' => [
                'abn_verification' => UploadedFile::fake()->create('abn.pdf', 100, 'application/pdf'),
                'police_check' => UploadedFile::fake()->create('policecheck.pdf', 100, 'application/pdf'),
                'ndis_worker_screening' => UploadedFile::fake()->create('screening.pdf', 100, 'application/pdf'),
                'insurance' => UploadedFile::fake()->create('insurance.pdf', 100, 'application/pdf'),
                'qualification' => UploadedFile::fake()->create('qualification.pdf', 100, 'application/pdf'),
                'first_aid_certificate' => UploadedFile::fake()->create('firstaid.pdf', 100, 'application/pdf'),
                'cpr_certificate' => UploadedFile::fake()->create('cpr.pdf', 100, 'application/pdf'),
                'registration' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertStatus(200);
        $response->assertSee('Stage 3: Document Review');
        $response->assertSee('AHHC is reviewing your documents');
    }

    public function test_worker_can_proceed_to_stage4_from_stage3_review_when_all_documents_are_uploaded(): void
    {
        Storage::fake('private');

        $worker = Worker::create([
            'worker_number' => 'W-1005',
            'first_name' => 'Proceed',
            'last_name' => 'Worker',
            'phone' => '0400111226',
            'email' => 'proceed.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 3,
            'onboarding_token' => 'proceed-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $requirements = [
            'ABN Verification',
            'Police Check',
            'NDIS Worker Screening',
            'Insurance',
            'Qualification',
            'First Aid Certificate',
            'CPR Certificate',
            'Registration',
        ];

        foreach ($requirements as $type) {
            $path = 'worker_compliance/'.$worker->id.'/'.Str::slug($type).'.pdf';
            Storage::disk('private')->put($path, 'PDF content');

            WorkerComplianceDocument::create([
                'worker_id' => $worker->id,
                'worker_compliance_type_id' => null,
                'document_type' => $type,
                'document_path' => $path,
                'status' => 'submitted',
            ]);
        }

        $response = $this->post(route('worker.onboarding.stage3.proceed', ['token' => $worker->onboarding_token]));

        $response->assertRedirect(route('worker.onboarding.show', ['token' => $worker->onboarding_token]));
        $response->assertSessionHas('success', 'All required compliance documents are uploaded. You may now sign the declarations.');

        $worker->refresh();
        $this->assertSame(4, $worker->onboarding_stage);
        $this->assertNotNull($worker->stage_3_completed_at);
    }

    public function test_admin_receives_notification_when_worker_submits_stage2_documents(): void
    {
        Storage::fake('private');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '0400111222',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $worker = Worker::create([
            'worker_number' => 'W-1003',
            'first_name' => 'Notify',
            'last_name' => 'Worker',
            'phone' => '0400111224',
            'email' => 'notify.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 2,
            'onboarding_token' => 'notify-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $response = $this->post(route('worker.onboarding.stage2.submit', ['token' => $worker->onboarding_token]), [
            'documents' => [
                'abn_verification' => UploadedFile::fake()->create('abn.pdf', 100, 'application/pdf'),
                'police_check' => UploadedFile::fake()->create('policecheck.pdf', 100, 'application/pdf'),
                'ndis_worker_screening' => UploadedFile::fake()->create('screening.pdf', 100, 'application/pdf'),
                'insurance' => UploadedFile::fake()->create('insurance.pdf', 100, 'application/pdf'),
                'qualification' => UploadedFile::fake()->create('qualification.pdf', 100, 'application/pdf'),
                'first_aid_certificate' => UploadedFile::fake()->create('firstaid.pdf', 100, 'application/pdf'),
                'cpr_certificate' => UploadedFile::fake()->create('cpr.pdf', 100, 'application/pdf'),
                'registration' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('worker.onboarding.show', ['token' => $worker->onboarding_token]));
        $response->assertSessionHas('success', 'Compliance documents submitted successfully. AHHC will review them and notify you when complete.');

        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $admin->id,
            'title' => 'Worker Submitted Compliance Documents',
            'type' => 'info',
        ]);
    }

    public function test_worker_can_preview_and_download_uploaded_stage2_documents(): void
    {
        Storage::fake('private');

        $worker = Worker::create([
            'worker_number' => 'W-1002',
            'first_name' => 'Preview',
            'last_name' => 'Worker',
            'phone' => '0400111223',
            'email' => 'preview.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 3,
            'onboarding_token' => 'preview-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $documentPath = 'worker_compliance/'.$worker->id.'/abn_preview.pdf';
        Storage::disk('private')->put($documentPath, 'PDF content');

        $document = WorkerComplianceDocument::create([
            'worker_id' => $worker->id,
            'worker_compliance_type_id' => null,
            'document_type' => 'ABN Verification',
            'document_path' => $documentPath,
            'status' => 'submitted',
        ]);

        $previewResponse = $this->get(route('worker.onboarding.document.preview', ['token' => $worker->onboarding_token, 'document' => $document->id]));
        $previewResponse->assertStatus(200);
        $previewResponse->assertHeader('Content-Type', 'application/pdf');

        $downloadResponse = $this->get(route('worker.onboarding.document.download', ['token' => $worker->onboarding_token, 'document' => $document->id]));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Disposition');
    }

    public function test_admin_can_approve_stage5_and_notify_worker_and_admins(): void
    {
        Mail::fake();
        Storage::fake('local');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '0400111222',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $workerUser = User::create([
            'name' => 'Worker User',
            'email' => 'worker@example.com',
            'phone' => '0400111223',
            'role' => 'worker',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $worker = Worker::create([
            'worker_number' => 'W-2001',
            'first_name' => 'Stage5',
            'last_name' => 'Worker',
            'phone' => '0400111229',
            'email' => 'stage5.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 5,
            'onboarding_token' => 'stage5-token-123',
            'onboarding_expires_at' => now()->addDays(30),
            'user_id' => $workerUser->id,
        ]);

        WorkerServiceApproval::create([
            'worker_id' => $worker->id,
            'service_category' => 'Personal Care',
            'description' => 'Approved category',
            'status' => 'approved',
            'approved_by_id' => $admin->id,
            'approved_at' => now(),
            'approval_start_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->post(route('admin.worker_onboarding.stage5.approve', ['worker' => $worker->id]));

        $this->assertDatabaseHas('workers', [
            'id' => $worker->id,
            'onboarding_stage' => 6,
        ]);

        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $workerUser->id,
            'title' => 'Onboarding Complete: Stage 6',
            'type' => 'success',
        ]);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $admin->id,
            'title' => 'Worker Onboarding Complete',
            'type' => 'success',
        ]);
    }

    public function test_admin_can_reject_worker_onboarding_and_notify_worker(): void
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '0400111222',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $workerUser = User::create([
            'name' => 'Worker User',
            'email' => 'worker@example.com',
            'phone' => '0400111223',
            'role' => 'worker',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $worker = Worker::create([
            'worker_number' => 'W-2002',
            'first_name' => 'Reject',
            'last_name' => 'Worker',
            'phone' => '0400111230',
            'email' => 'reject.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 5,
            'onboarding_token' => 'reject-token-123',
            'onboarding_expires_at' => now()->addDays(30),
            'user_id' => $workerUser->id,
        ]);

        $this->actingAs($admin)->post(route('admin.worker_onboarding.reject', ['worker' => $worker->id]), [
            'rejection_reason' => 'Not eligible at this time.',
        ]);

        $this->assertDatabaseHas('workers', [
            'id' => $worker->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $workerUser->id,
            'title' => 'Worker Onboarding Rejected',
            'type' => 'warning',
        ]);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $admin->id,
            'title' => 'Worker Onboarding Rejected',
            'type' => 'warning',
        ]);
    }

    public function test_worker_stage4_shows_admin_assigned_onboarding_documents(): void
    {
        Storage::fake('local');

        $worker = Worker::create([
            'worker_number' => 'W-1006',
            'first_name' => 'Assigned',
            'last_name' => 'Worker',
            'phone' => '0400111227',
            'email' => 'assigned.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 4,
            'onboarding_token' => 'assigned-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $path = 'documents/worker_agreement.pdf';
        Storage::disk('local')->put($path, 'PDF content');

        Document::create([
            'owner_type' => Worker::class,
            'owner_id' => $worker->id,
            'document_type' => 'agreement',
            'description' => 'Worker agreement required for onboarding',
            'title' => 'Worker Agreement',
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'uploaded_by_id' => null,
            'status' => 'active',
            'onboarding_required' => true,
        ]);

        $response = $this->get(route('worker.onboarding.show', ['token' => $worker->onboarding_token]));
        $response->assertStatus(200);
        $response->assertSee('Required Agreement Forms');
        $response->assertSee('Worker Agreement');
    }

    public function test_worker_can_preview_and_download_assigned_onboarding_documents(): void
    {
        Storage::fake('local');

        $worker = Worker::create([
            'worker_number' => 'W-1007',
            'first_name' => 'Assigned',
            'last_name' => 'Worker2',
            'phone' => '0400111228',
            'email' => 'assigned2.worker@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 4,
            'onboarding_token' => 'assigned2-token-123',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $path = 'documents/worker_agreement2.pdf';
        Storage::disk('local')->put($path, 'PDF content');

        $document = Document::create([
            'owner_type' => Worker::class,
            'owner_id' => $worker->id,
            'document_type' => 'agreement',
            'description' => 'Worker agreement required for onboarding',
            'title' => 'Worker Agreement 2',
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'uploaded_by_id' => null,
            'status' => 'active',
            'onboarding_required' => true,
        ]);

        $previewResponse = $this->get(route('worker.onboarding.assigned_document.preview', ['token' => $worker->onboarding_token, 'document' => $document->id]));
        $previewResponse->assertStatus(200);
        $previewResponse->assertHeader('Content-Type', 'application/pdf');

        $downloadResponse = $this->get(route('worker.onboarding.assigned_document.download', ['token' => $worker->onboarding_token, 'document' => $document->id]));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Disposition');
    }
}
