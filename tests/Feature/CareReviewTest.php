<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\MonthlyCareReview;
use App\Models\Participant;
use App\Models\User;
use App\Services\CareReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $careManager;

    protected User $admin;

    protected Participant $participant;

    protected CareReviewService $reviewService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->careManager = User::factory()->create(['role' => 'care_manager']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->participant = Participant::factory()->create();
        $this->reviewService = app(CareReviewService::class);
    }

    public function test_can_create_review()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value
        );

        $this->assertInstanceOf(MonthlyCareReview::class, $review);
        $this->assertEquals($this->participant->id, $review->participant_id);
        $this->assertEquals($this->careManager->id, $review->care_manager_id);
        $this->assertEquals(ReviewStatus::DUE->value, $review->status);
    }

    public function test_default_next_review_is_30_days()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager
        );

        $expectedDate = now()->addMonth()->toDateString();
        $this->assertEquals($expectedDate, $review->next_review_date->toDateString());
    }

    public function test_can_complete_review()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager
        );

        $completedReview = $this->reviewService->completeReview(
            $review,
            $this->admin,
            'Review completed successfully'
        );

        $this->assertEquals(ReviewStatus::COMPLETED->value, $completedReview->status);
        $this->assertEquals($this->admin->id, $completedReview->completed_by_id);
        $this->assertNotNull($completedReview->completed_at);
    }

    public function test_completion_schedules_next_review()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager
        );

        $completedReview = $this->reviewService->completeReview($review, $this->admin);

        // Next review should be 30 days from completion
        $expectedDate = now()->addMonth()->toDateString();
        $this->assertEquals($expectedDate, $completedReview->next_review_date->toDateString());
    }

    public function test_can_add_concerns()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);
        $concern = 'Participant reported increased anxiety';

        $updatedReview = $this->reviewService->addConcerns($review, $concern);

        $this->assertStringContainsString($concern, $updatedReview->concerns);
    }

    public function test_can_add_actions_required()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);
        $action = 'Schedule psychological assessment';

        $updatedReview = $this->reviewService->addActionsRequired($review, $action);

        $this->assertStringContainsString($action, $updatedReview->actions_required);
    }

    public function test_can_schedule_next_review()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);
        $newDate = now()->addWeeks(2)->toDateString();

        $updatedReview = $this->reviewService->scheduleNextReview($review, $newDate);

        $this->assertEquals($newDate, $updatedReview->next_review_date->toDateString());
    }

    public function test_is_due_correctly_identifies_due_reviews()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->toDateString()
        );

        $this->assertTrue($review->isDue());
    }

    public function test_is_overdue_correctly_identifies_overdue_reviews()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->subDay()->toDateString()
        );

        $this->assertTrue($review->isOverdue());
    }

    public function test_days_until_due_calculation()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->addDays(5)->toDateString()
        );

        $this->assertEquals(5, $review->daysUntilDue());
    }

    public function test_days_overdue_calculation()
    {
        $review = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->subDays(3)->toDateString()
        );

        $this->assertEquals(3, $review->daysOverdue());
    }

    public function test_scan_and_update_review_statuses()
    {
        // Create some reviews
        $overdueReview = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->subDay()->toDateString()
        );

        $results = $this->reviewService->scanAndUpdateReviewStatuses();

        $updatedReview = $overdueReview->fresh();
        $this->assertEquals(ReviewStatus::OVERDUE->value, $updatedReview->status);
        $this->assertGreaterThan(0, $results['updated']);
    }

    public function test_activity_logging_on_creation()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);

        $activities = $review->activities;
        $this->assertGreaterThan(0, $activities->count());
        $this->assertTrue($activities->contains('activity_type', 'created'));
    }

    public function test_activity_logging_on_completion()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);
        $this->reviewService->completeReview($review, $this->admin);

        $activities = $review->fresh()->activities;
        $this->assertTrue($activities->contains('activity_type', 'completed'));
    }

    public function test_get_reviews_due_soon()
    {
        // Create reviews with different due dates
        $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->addDays(3)->toDateString()
        );

        $dueReviews = $this->reviewService->getReviewsDueSoon(7);
        $this->assertGreaterThan(0, $dueReviews->count());
    }

    public function test_get_all_due_reviews()
    {
        // Create multiple due reviews
        $review1 = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->toDateString()
        );

        $reviews = $this->reviewService->getAllDueReviews();
        $this->assertGreaterThan(0, $reviews->count());
        $this->assertTrue($reviews->contains('id', $review1->id));
    }

    public function test_get_overdue_reviews()
    {
        $overdueReview = $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->subDay()->toDateString()
        );

        // Manually update status since scan wouldn't run
        $overdueReview->update(['status' => ReviewStatus::OVERDUE->value]);

        $reviews = $this->reviewService->getOverdueReviews();
        $this->assertGreaterThan(0, $reviews->count());
        $this->assertTrue($reviews->contains('id', $overdueReview->id));
    }

    public function test_can_get_reviews_for_participant()
    {
        $review1 = $this->reviewService->createReview($this->participant, $this->careManager);

        $otherParticipant = Participant::factory()->create();
        $this->reviewService->createReview($otherParticipant, $this->careManager);

        $reviews = MonthlyCareReview::forParticipant($this->participant->id)->get();

        $this->assertEquals(1, $reviews->count());
        $this->assertEquals($review1->id, $reviews->first()->id);
    }

    public function test_can_get_reviews_for_care_manager()
    {
        $review1 = $this->reviewService->createReview($this->participant, $this->careManager);

        $otherManager = User::factory()->create(['role' => 'care_manager']);
        $this->reviewService->createReview($this->participant, $otherManager);

        $reviews = MonthlyCareReview::forCareManager($this->careManager->id)->get();

        $this->assertGreaterThan(0, $reviews->count());
        $this->assertTrue($reviews->contains('id', $review1->id));
    }

    public function test_api_can_list_reviews()
    {
        $this->reviewService->createReview($this->participant, $this->careManager);

        $response = $this->actingAs($this->admin)
            ->getJson('/portal/admin/care-reviews');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_api_can_show_review()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);

        $response = $this->actingAs($this->admin)
            ->getJson("/portal/admin/care-reviews/{$review->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $review->id);
    }

    public function test_api_can_complete_review()
    {
        $review = $this->reviewService->createReview($this->participant, $this->careManager);

        $response = $this->actingAs($this->admin)
            ->postJson("/portal/admin/care-reviews/{$review->id}/complete", [
                'completion_notes' => 'Review completed successfully',
            ]);

        $response->assertOk();
        $this->assertEquals(ReviewStatus::COMPLETED->value, $review->fresh()->status);
    }

    public function test_api_can_get_dashboard_stats()
    {
        $this->reviewService->createReview($this->participant, $this->careManager);

        $response = $this->actingAs($this->admin)
            ->getJson('/portal/admin/care-reviews/dashboard/stats');

        $response->assertOk();
        $response->assertJsonStructure([
            'reviews_due',
            'reviews_completed',
            'reviews_overdue',
            'compliance_rate',
        ]);
    }

    public function test_can_export_outstanding_reviews()
    {
        $this->reviewService->createReview(
            $this->participant,
            $this->careManager,
            ReviewType::STANDARD->value,
            now()->toDateString()
        );

        $response = $this->actingAs($this->admin)
            ->getJson('/portal/admin/care-reviews/export/outstanding');

        $response->assertOk();
        $response->assertHeaderContains('Content-Type', 'text/csv');
    }
}
