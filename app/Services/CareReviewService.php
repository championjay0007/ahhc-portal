<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\CareContactLog;
use App\Models\CareReviewActivity;
use App\Models\MonthlyCareReview;
use App\Models\Participant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CareReviewService
{
    /**
     * Create a monthly care review
     */
    public function createReview(
        Participant $participant,
        User $careManager,
        ?string $reviewType = 'Standard',
        ?string $nextReviewDate = null
    ): MonthlyCareReview {
        // Default next review date to 30 days from now
        $nextReview = $nextReviewDate ? Carbon::parse($nextReviewDate) : now()->addMonth();

        $review = MonthlyCareReview::create([
            'participant_id' => $participant->id,
            'care_manager_id' => $careManager->id,
            'review_type' => $reviewType ?? ReviewType::STANDARD->value,
            'next_review_date' => $nextReview->toDateString(),
            'status' => ReviewStatus::DUE->value,
        ]);

        // Log the activity
        $this->logActivity($review, auth()->id() ?? $careManager->id, 'created', 'Monthly care review created');

        return $review;
    }

    /**
     * Complete a review
     */
    public function completeReview(
        MonthlyCareReview $review,
        User $completedBy,
        string $notes = '',
        string $nextReviewDate = ''
    ): MonthlyCareReview {
        $nextReview = $nextReviewDate ? Carbon::parse($nextReviewDate) : now()->addMonth();

        $review->update([
            'status' => ReviewStatus::COMPLETED->value,
            'review_date' => now()->toDateString(),
            'completed_at' => now(),
            'completed_by_id' => $completedBy->id,
            'completion_notes' => $notes,
            'next_review_date' => $nextReview->toDateString(),
        ]);

        // Log the activity
        $this->logActivity($review, $completedBy->id, 'completed', 'Review completed');

        return $review;
    }

    /**
     * Schedule next review
     */
    public function scheduleNextReview(MonthlyCareReview $review, string $nextReviewDate): MonthlyCareReview
    {
        $review->update(['next_review_date' => $nextReviewDate]);

        $this->logActivity(
            $review,
            auth()->id() ?? $review->care_manager_id,
            'updated',
            "Next review scheduled for {$nextReviewDate}"
        );

        return $review->fresh();
    }

    /**
     * Add concerns to review
     */
    public function addConcerns(MonthlyCareReview $review, string $concerns): MonthlyCareReview
    {
        $existing = $review->concerns ? $review->concerns."\n\n---\n" : '';
        $review->update(['concerns' => $existing.$concerns]);

        $this->logActivity($review, auth()->id() ?? $review->care_manager_id, 'updated', 'Concerns added to review');

        return $review->fresh();
    }

    /**
     * Add actions required
     */
    public function addActionsRequired(MonthlyCareReview $review, string $actions): MonthlyCareReview
    {
        $existing = $review->actions_required ? $review->actions_required."\n\n---\n" : '';
        $review->update(['actions_required' => $existing.$actions]);

        $this->logActivity($review, auth()->id() ?? $review->care_manager_id, 'updated', 'Actions required added to review');

        return $review->fresh();
    }

    /**
     * Get reviews due within specified days
     */
    public function getReviewsDueSoon(int $days = 7): Collection
    {
        $targetDate = now()->addDays($days);

        return MonthlyCareReview::where('status', ReviewStatus::DUE->value)
            ->whereDate('next_review_date', '<=', $targetDate)
            ->whereDate('next_review_date', '>=', now())
            ->with('participant', 'careManager')
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Get reviews due today
     */
    public function getReviewsDueToday(): Collection
    {
        return MonthlyCareReview::where('status', ReviewStatus::DUE->value)
            ->whereDate('next_review_date', now()->toDateString())
            ->with('participant', 'careManager')
            ->get();
    }

    /**
     * Get overdue reviews
     */
    public function getOverdueReviews(): Collection
    {
        return MonthlyCareReview::where('status', ReviewStatus::OVERDUE->value)
            ->with('participant', 'careManager')
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Get all due reviews
     */
    public function getAllDueReviews(): Collection
    {
        return MonthlyCareReview::where('status', ReviewStatus::DUE->value)
            ->with('participant', 'careManager')
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Get all completed reviews
     */
    public function getCompletedReviews(): Collection
    {
        return MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)
            ->with('participant', 'careManager', 'completedBy')
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * Get reviews for a participant
     */
    public function getParticipantReviews(Participant $participant): Collection
    {
        return $participant->monthlyReviews()
            ->with('careManager', 'completedBy')
            ->orderByDesc('next_review_date')
            ->get();
    }

    /**
     * Get reviews for a care manager
     */
    public function getCareManagerReviews(User $careManager): Collection
    {
        return $careManager->managedReviews()
            ->with('participant')
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Get reviews for a care manager by status
     */
    public function getCareManagerReviewsByStatus(User $careManager, string $status): Collection
    {
        return $careManager->managedReviews()
            ->where('status', $status)
            ->with('participant')
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Scan all reviews and update status (Due → Overdue)
     */
    public function scanAndUpdateReviewStatuses(): array
    {
        $results = [
            'updated' => 0,
            'overdue' => 0,
            'completed' => 0,
        ];

        // Mark overdue reviews
        $overdueReviews = MonthlyCareReview::where('status', ReviewStatus::DUE->value)
            ->whereDate('next_review_date', '<', now())
            ->get();

        foreach ($overdueReviews as $review) {
            $review->update(['status' => ReviewStatus::OVERDUE->value]);
            $this->logActivity($review, auth()->id() ?? $review->care_manager_id, 'system', 'Review marked as overdue');
            $results['overdue']++;
            $results['updated']++;
        }

        return $results;
    }

    /**
     * Log activity for a review
     */
    public function logActivity(
        MonthlyCareReview $review,
        ?int $userId,
        string $activityType,
        string $description,
        ?array $changes = null
    ): CareReviewActivity {
        return $review->activities()->create([
            'user_id' => $userId ?? $review->care_manager_id,
            'activity_type' => $activityType,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get review activity log
     */
    public function getReviewActivityLog(MonthlyCareReview $review): Collection
    {
        return $review->activities()
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Add contact log entry
     */
    public function addContactLog(
        Participant $participant,
        User $careManager,
        string $contactType,
        string $contactMethod,
        ?string $notes = null,
        ?string $outcomes = null,
        bool $followUpRequired = false,
        ?string $followUpDate = null,
        ?MonthlyCareReview $review = null
    ): CareContactLog {
        return CareContactLog::create([
            'participant_id' => $participant->id,
            'care_manager_id' => $careManager->id,
            'monthly_care_review_id' => $review?->id,
            'contact_datetime' => now(),
            'contact_type' => $contactType,
            'contact_method' => $contactMethod,
            'notes' => $notes,
            'outcomes' => $outcomes,
            'follow_up_required' => $followUpRequired,
            'follow_up_date' => $followUpDate ? Carbon::parse($followUpDate)->toDateString() : null,
        ]);
    }

    /**
     * Get contact logs for a participant
     */
    public function getParticipantContactLogs(Participant $participant): Collection
    {
        return $participant->contactLogs()
            ->with('careManager')
            ->recentFirst()
            ->get();
    }

    /**
     * Get contact logs for a care manager
     */
    public function getCareManagerContactLogs(User $careManager): Collection
    {
        return $careManager->managedContactLogs()
            ->with('participant')
            ->recentFirst()
            ->get();
    }

    /**
     * Get contact logs that need follow up
     */
    public function getContactLogsNeedingFollowUp(): Collection
    {
        return CareContactLog::withFollowUp()
            ->where('follow_up_date', '<=', now()->toDateString())
            ->with('participant', 'careManager')
            ->recentFirst()
            ->get();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'reviews_due' => MonthlyCareReview::where('status', ReviewStatus::DUE->value)->count(),
            'reviews_completed' => MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)->count(),
            'reviews_overdue' => MonthlyCareReview::where('status', ReviewStatus::OVERDUE->value)->count(),
            'in_progress' => MonthlyCareReview::where('status', ReviewStatus::IN_PROGRESS->value)->count(),
            'reviews_due_7_days' => $this->getReviewsDueSoon(7)->count(),
            'reviews_due_today' => $this->getReviewsDueToday()->count(),
            'participants_with_reviews' => MonthlyCareReview::distinct('participant_id')->count(),
            'total_reviews' => MonthlyCareReview::count(),
        ];
    }

    /**
     * Get participant review status
     */
    public function getParticipantReviewStatus(Participant $participant): array
    {
        $reviews = $participant->monthlyReviews;

        return [
            'last_review' => $reviews->where('status', ReviewStatus::COMPLETED->value)->sortByDesc('completed_at')->first(),
            'next_review_due' => $reviews->where('status', ReviewStatus::DUE->value)->sortBy('next_review_date')->first(),
            'overdue_reviews' => $reviews->where('status', ReviewStatus::OVERDUE->value),
            'total_reviews' => $reviews->count(),
            'completion_rate' => $reviews->count() > 0
                ? round(($reviews->where('status', ReviewStatus::COMPLETED->value)->count() / $reviews->count()) * 100, 2)
                : 0,
        ];
    }
}
