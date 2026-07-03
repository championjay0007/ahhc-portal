<?php

namespace Tests\Feature;

use App\Enums\WorkerDeclarationType;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerComplianceType;
use App\Models\WorkerDeclaration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApproveStage2Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_approve_stage2_when_required_documents_missing(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $worker = Worker::create([
            'worker_number' => 'W-2001',
            'first_name' => 'NoDocs',
            'last_name' => 'Worker',
            'phone' => '0400000000',
            'email' => 'nodocs@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 2,
            'onboarding_token' => 'token-approve-2',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.worker_onboarding.stage2.approve', $worker));

        $response->assertSessionHasErrors('documents');
        $worker->refresh();
        $this->assertSame(2, $worker->onboarding_stage);
    }

    public function test_admin_can_approve_stage2_when_required_documents_present(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin3@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $worker = Worker::create([
            'worker_number' => 'W-2002',
            'first_name' => 'HasDocs',
            'last_name' => 'Worker',
            'phone' => '0400000001',
            'email' => 'hasdocs@example.com',
            'role_type' => 'Independent',
            'status' => 'pending',
            'onboarding_stage' => 2,
            'onboarding_token' => 'token-approve-3',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        // Ensure defaults exist then create a submitted document for each required type
        WorkerComplianceType::ensureDefaults();
        $required = WorkerComplianceType::where('is_required', true)->pluck('name')->toArray();

        foreach ($required as $typeName) {
            WorkerComplianceDocument::create([
                'worker_id' => $worker->id,
                'document_type' => $typeName,
                'document_path' => 'worker_compliance/'.$worker->id.'/sample.pdf',
                'status' => 'submitted',
            ]);
        }

        $response = $this->actingAs($admin)
            ->post(route('admin.worker_onboarding.stage2.approve', $worker), [
                'notes' => 'All good',
            ]);

        $response->assertRedirect(route('admin.worker_onboarding.show', $worker));
        $worker->refresh();
        $this->assertSame(3, $worker->onboarding_stage);
        $this->assertNotNull($worker->stage_2_completed_at);
    }

    public function test_admin_show_page_displays_worker_profile_documents_and_declarations(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin4@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $worker = Worker::create([
            'worker_number' => 'W-2003',
            'first_name' => 'Profile',
            'last_name' => 'Worker',
            'phone' => '0400000002',
            'email' => 'profile@example.com',
            'role_type' => 'Independent',
            'qualification' => 'Certificate IV in Disability Support',
            'availability' => 'Weekdays',
            'vehicle_type' => 'Car',
            'notes' => 'Prefers morning shifts',
            'status' => 'pending',
            'onboarding_stage' => 2,
            'onboarding_token' => 'token-approve-4',
            'onboarding_expires_at' => now()->addDays(30),
        ]);

        WorkerComplianceDocument::create([
            'worker_id' => $worker->id,
            'document_type' => 'Police Check',
            'document_path' => 'worker_compliance/'.$worker->id.'/police.pdf',
            'status' => 'submitted',
            'issue_date' => now()->subYear(),
            'expiry_date' => now()->addYear(),
            'notes' => 'Submitted for review',
        ]);

        WorkerDeclaration::create([
            'worker_id' => $worker->id,
            'declaration_type' => WorkerDeclarationType::PRIVACY,
            'declaration_text' => 'I agree to protect participant privacy.',
            'agreed' => true,
            'signed_at' => now(),
            'signature_file_path' => 'signatures/'.$worker->id.'/privacy.png',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.worker_onboarding.show', $worker));

        $response->assertOk();
        $response->assertSee($worker->qualification);
        $response->assertSee($worker->availability);
        $response->assertSee($worker->vehicle_type);
        $response->assertSee('Police Check');
        $response->assertSee('I agree to protect participant privacy.');
        $response->assertSee('Preview');
    }
}
