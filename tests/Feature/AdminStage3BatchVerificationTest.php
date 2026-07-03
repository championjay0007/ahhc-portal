<?php

namespace Tests\Feature;

use App\Enums\WorkerDeclarationType;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerComplianceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStage3BatchVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_verify_marks_documents_active_and_rejected(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin4@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        WorkerComplianceType::ensureDefaults();

        $worker = Worker::create([
            'worker_number' => 'W-3001',
            'first_name' => 'Batch',
            'last_name' => 'Tester',
            'phone' => '0400000002',
            'email' => 'batch@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 3,
            'onboarding_token' => 'token-batch-1',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $types = WorkerComplianceType::all();

        $docs = [];
        foreach ($types->take(3) as $type) {
            $docs[] = WorkerComplianceDocument::create([
                'worker_id' => $worker->id,
                'document_type' => $type->name,
                'document_path' => 'worker_compliance/'.$worker->id.'/file.pdf',
                'status' => 'submitted',
            ]);
        }

        $payload = ['documents' => []];
        $payload['documents'][] = ['id' => $docs[0]->id, 'action' => 'active'];
        $payload['documents'][] = ['id' => $docs[1]->id, 'action' => 'reject', 'reason' => 'Invalid'];

        $response = $this->actingAs($admin)
            ->post(route('admin.worker_onboarding.stage3.verify', $worker), $payload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('worker_compliance_documents', [
            'id' => $docs[0]->id,
            'status' => 'Active',
        ]);

        $this->assertDatabaseHas('worker_compliance_documents', [
            'id' => $docs[1]->id,
            'status' => 'Rejected',
            'rejection_reason' => 'Invalid',
        ]);
    }

    public function test_auto_approve_advances_stage_and_creates_declarations(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin5@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        WorkerComplianceType::ensureDefaults();

        $worker = Worker::create([
            'worker_number' => 'W-3002',
            'first_name' => 'Auto',
            'last_name' => 'Approve',
            'phone' => '0400000003',
            'email' => 'autoapprove@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 3,
            'onboarding_token' => 'token-batch-2',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $required = WorkerComplianceType::where('is_required', true)->get();

        foreach ($required as $type) {
            WorkerComplianceDocument::create([
                'worker_id' => $worker->id,
                'document_type' => $type->name,
                'document_path' => 'worker_compliance/'.$worker->id.'/file.pdf',
                'status' => 'submitted',
            ]);
        }

        $payload = ['documents' => [], 'auto_approve' => 1];
        foreach ($worker->complianceDocuments as $doc) {
            $payload['documents'][] = ['id' => $doc->id, 'action' => 'active'];
        }

        $response = $this->actingAs($admin)
            ->post(route('admin.worker_onboarding.stage3.verify', $worker), $payload);

        $response->assertRedirect();

        $worker->refresh();
        $this->assertSame(4, $worker->onboarding_stage);
        $this->assertNotNull($worker->stage_3_completed_at);

        // verify declarations created
        $this->assertDatabaseCount('worker_declarations', count(WorkerDeclarationType::cases()));
    }
}
