<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkerOnboardingStage4AdminDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_stage4_shows_admin_uploaded_documents_and_can_preview_them(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Worker One',
            'email' => 'worker1@example.com',
            'role' => 'worker',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $worker = Worker::create([
            'user_id' => $user->id,
            'first_name' => 'Worker',
            'last_name' => 'One',
            'email' => 'worker1@example.com',
            'role_type' => 'worker',
            'status' => 'active',
            'worker_number' => 'W-1001',
            'onboarding_stage' => 4,
            'onboarding_token' => 'test-token',
            'onboarding_expires_at' => now()->addDays(7),
        ]);

        $filePath = 'documents/test-declaration.pdf';
        Storage::disk('local')->put($filePath, 'dummy pdf content');

        $document = Document::create([
            'owner_type' => Worker::class,
            'owner_id' => $worker->id,
            'document_type' => 'Worker Agreement',
            'description' => 'Test admin assigned worker agreement',
            'title' => 'Worker Agreement Form',
            'storage_disk' => 'local',
            'path' => $filePath,
            'mime_type' => 'application/pdf',
            'size_bytes' => Storage::disk('local')->size($filePath),
            'uploaded_by_id' => $user->id,
            'status' => 'active',
            'onboarding_required' => true,
            'expires_at' => now()->addDays(30),
            'is_sensitive' => false,
            'metadata' => [],
        ]);

        $response = $this->get(route('worker.onboarding.show', ['token' => $worker->onboarding_token]));

        $response->assertStatus(200);
        $response->assertSee('Required Agreement Forms');
        $response->assertSee('Worker Agreement Form');
        $response->assertSee(route('worker.onboarding.assigned_document.preview', ['token' => $worker->onboarding_token, 'document' => $document->id]));

        $previewResponse = $this->get(route('worker.onboarding.assigned_document.preview', ['token' => $worker->onboarding_token, 'document' => $document->id]));
        $previewResponse->assertStatus(200);
        $previewResponse->assertHeader('Content-Type', 'application/pdf');
    }
}
