<?php

namespace Tests\Feature;

use App\Enums\ComplianceDocumentType;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Services\ComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceTrackingTest extends TestCase
{
    use RefreshDatabase;

    private ComplianceService $complianceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->complianceService = app(ComplianceService::class);
    }

    /**
     * Test worker compliance initialization
     */
    public function test_worker_compliance_initialization(): void
    {
        $worker = Worker::factory()->create();

        $this->complianceService->initializeWorkerCompliance($worker);

        $this->assertCount(9, $worker->complianceDocuments);
        $this->assertTrue(
            $worker->complianceDocuments()->where('status', 'Missing')->count() === 9
        );
    }

    /**
     * Test creating and updating compliance document
     */
    public function test_create_compliance_document(): void
    {
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::POLICE_CHECK,
            issueDate: now()->subYear(),
            expiryDate: now()->addYear(),
            notes: 'Test document'
        );

        $this->assertNotNull($document);
        $this->assertEquals('Police Check', $document->document_type);
        $this->assertEquals('Active', $document->status);
        $this->assertEquals('Test document', $document->notes);
    }

    /**
     * Test status updates for expiring documents
     */
    public function test_expiring_soon_status(): void
    {
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::NDIS_WORKER_SCREENING,
            issueDate: now()->subYear(),
            expiryDate: now()->addDays(15) // 15 days from now
        );

        $this->complianceService->updateDocumentStatus($document);
        $this->assertEquals('Expiring Soon', $document->fresh()->status);
    }

    /**
     * Test status updates for expired documents
     */
    public function test_expired_status(): void
    {
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::INSURANCE,
            issueDate: now()->subYears(2),
            expiryDate: now()->subDays(5) // 5 days ago
        );

        $this->complianceService->updateDocumentStatus($document);
        $this->assertEquals('Expired', $document->fresh()->status);
    }

    /**
     * Test worker assignability with active documents
     */
    public function test_can_assign_worker_with_active_documents(): void
    {
        $worker = Worker::factory()->create();
        $this->complianceService->initializeWorkerCompliance($worker);

        // Set critical documents to active
        $worker->complianceDocuments()
            ->whereIn('document_type', [
                'Police Check',
                'NDIS Worker Screening',
                'Insurance',
            ])
            ->update(['status' => 'Active']);

        $this->assertTrue($this->complianceService->canWorkerBeAssigned($worker));
        $this->assertNull($this->complianceService->getAssignmentBlockingReason($worker));
    }

    /**
     * Test worker cannot be assigned with expired critical document
     */
    public function test_cannot_assign_worker_with_expired_document(): void
    {
        $worker = Worker::factory()->create();
        $this->complianceService->initializeWorkerCompliance($worker);

        // Expire critical document
        $worker->complianceDocuments()
            ->where('document_type', 'Police Check')
            ->update(['status' => 'Expired']);

        $this->assertFalse($this->complianceService->canWorkerBeAssigned($worker));
        $reason = $this->complianceService->getAssignmentBlockingReason($worker);
        $this->assertStringContainsString('Police Check', $reason);
        $this->assertStringContainsString('Expired', $reason);
    }

    /**
     * Test get expiring documents
     */
    public function test_get_expiring_documents(): void
    {
        $worker = Worker::factory()->create();

        // Create expiring document
        $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::POLICE_CHECK,
            issueDate: now()->subYear(),
            expiryDate: now()->addDays(15)
        );

        $expiringDocs = $this->complianceService->getExpiringDocuments();

        $this->assertCount(1, $expiringDocs);
    }

    /**
     * Test compliance dashboard statistics
     */
    public function test_dashboard_statistics(): void
    {
        $worker1 = Worker::factory()->create();
        $worker2 = Worker::factory()->create();

        // Setup worker 1 with mixed statuses
        $this->complianceService->createOrUpdateDocument(
            worker: $worker1,
            documentType: ComplianceDocumentType::POLICE_CHECK,
            issueDate: now()->subYear(),
            expiryDate: now()->addDays(15)
        );

        $this->complianceService->createOrUpdateDocument(
            worker: $worker1,
            documentType: ComplianceDocumentType::INSURANCE,
            issueDate: now()->subYears(2),
            expiryDate: now()->subDays(5)
        );

        $stats = $this->complianceService->getDashboardStats();

        $this->assertGreaterThan(0, $stats['expiring_soon']);
        $this->assertGreaterThan(0, $stats['expired']);
        $this->assertGreaterThanOrEqual(1, $stats['workers_with_issues']);
    }

    /**
     * Test document deletion
     */
    public function test_delete_document(): void
    {
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::QUALIFICATION,
            issueDate: now()->subYear(),
            expiryDate: now()->addYear()
        );

        $documentId = $document->id;

        $this->assertTrue($this->complianceService->deleteDocument($document));
        $this->assertNull(WorkerComplianceDocument::find($documentId));
    }

    /**
     * Test mark document as verified
     */
    public function test_mark_document_as_verified(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::POLICE_CHECK,
            issueDate: now()->subYear(),
            expiryDate: now()->addYear()
        );

        $document->markAsVerified($user);

        $this->assertEquals('Active', $document->fresh()->status);
        $this->assertEquals($user->id, $document->fresh()->verified_by_id);
        $this->assertNotNull($document->fresh()->verified_at);
    }

    /**
     * Test mark document as rejected
     */
    public function test_mark_document_as_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::POLICE_CHECK,
            issueDate: now()->subYear(),
            expiryDate: now()->addYear()
        );

        $reason = 'Document is forged';
        $document->markAsRejected($user, $reason);

        $this->assertEquals('Rejected', $document->fresh()->status);
        $this->assertEquals($reason, $document->fresh()->rejection_reason);
        $this->assertNotNull($document->fresh()->rejected_at);
    }

    /**
     * Test days until expiry calculation
     */
    public function test_days_until_expiry(): void
    {
        $worker = Worker::factory()->create();

        $document = $this->complianceService->createOrUpdateDocument(
            worker: $worker,
            documentType: ComplianceDocumentType::CPR_CERTIFICATE,
            issueDate: now()->subYear(),
            expiryDate: now()->addDays(10)
        );

        $this->assertEquals(10, $document->daysUntilExpiry());
    }

    /**
     * Test scan all workers compliance
     */
    public function test_scan_all_workers_compliance(): void
    {
        Worker::factory(3)->create();

        $results = $this->complianceService->scanAllWorkerCompliance();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('processed', $results);
        $this->assertArrayHasKey('workers_affected', $results);
    }

    /**
     * Test API endpoint for dashboard
     */
    public function test_compliance_dashboard_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/portal/admin/compliance/dashboard');

        $response->assertOk();
        $response->assertJsonStructure([
            'total_workers',
            'expiring_soon',
            'expired',
            'missing',
            'compliance_score',
        ]);
    }
}
