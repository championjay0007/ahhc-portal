<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\CareContactLog;
use App\Models\MonthlyCareReview;
use App\Models\User;

class CareReviewDashboardService
{
    public function __construct(private CareReviewService $careReviewService) {}

    /**
     * Get all dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'reviews_due' => $this->getReviewsDue(),
            'reviews_completed' => $this->getReviewsCompleted(),
            'reviews_overdue' => $this->getReviewsOverdue(),
            'reviews_due_7_days' => $this->careReviewService->getReviewsDueSoon(7),
            'reviews_due_today' => $this->careReviewService->getReviewsDueToday(),
            'participants_with_reviews' => $this->getParticipantsWithReviews(),
            'compliance_rate' => $this->getComplianceRate(),
            'average_days_overdue' => $this->getAverageDaysOverdue(),
            'total_contact_logs' => $this->getTotalContactLogs(),
        ];
    }

    /**
     * Get reviews due
     */
    public function getReviewsDue(): array
    {
        $reviews = MonthlyCareReview::where('status', ReviewStatus::DUE->value)
            ->with('participant', 'careManager')
            ->get();

        return [
            'count' => $reviews->count(),
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'participant_name' => $review->participant->first_name.' '.$review->participant->last_name,
                    'care_manager' => $review->careManager->name,
                    'due_date' => $review->next_review_date->toDateString(),
                    'review_type' => $review->review_type,
                    'days_until_due' => $review->daysUntilDue(),
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get reviews completed
     */
    public function getReviewsCompleted(): array
    {
        $reviews = MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)
            ->with('participant', 'careManager', 'completedBy')
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        return [
            'count' => MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)->count(),
            'recent' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'participant_name' => $review->participant->first_name.' '.$review->participant->last_name,
                    'care_manager' => $review->careManager->name,
                    'completed_by' => $review->completedBy?->name,
                    'completed_date' => $review->completed_at?->toDateString(),
                    'review_type' => $review->review_type,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get reviews overdue
     */
    public function getReviewsOverdue(): array
    {
        $reviews = MonthlyCareReview::where('status', ReviewStatus::OVERDUE->value)
            ->with('participant', 'careManager')
            ->orderBy('next_review_date')
            ->get();

        return [
            'count' => $reviews->count(),
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'participant_name' => $review->participant->first_name.' '.$review->participant->last_name,
                    'care_manager' => $review->careManager->name,
                    'due_date' => $review->next_review_date->toDateString(),
                    'days_overdue' => $review->daysOverdue(),
                    'review_type' => $review->review_type,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get total participants with reviews
     */
    public function getParticipantsWithReviews(): int
    {
        return MonthlyCareReview::distinct('participant_id')->count();
    }

    /**
     * Calculate compliance rate
     */
    public function getComplianceRate(): array
    {
        $completedCount = MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)->count();
        $totalCount = MonthlyCareReview::count();

        $rate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 2) : 0;

        return [
            'rate' => $rate,
            'completed' => $completedCount,
            'total' => $totalCount,
        ];
    }

    /**
     * Get average days overdue
     */
    public function getAverageDaysOverdue(): float
    {
        $overdueReviews = MonthlyCareReview::where('status', ReviewStatus::OVERDUE->value)->get();

        if ($overdueReviews->isEmpty()) {
            return 0;
        }

        $totalDaysOverdue = $overdueReviews->sum(fn ($review) => $review->daysOverdue() ?? 0);

        return round($totalDaysOverdue / $overdueReviews->count(), 2);
    }

    /**
     * Get total contact logs
     */
    public function getTotalContactLogs(): int
    {
        return CareContactLog::count();
    }

    /**
     * Get monthly review completion report
     */
    public function getMonthlyCompletionReport(string $month = ''): array
    {
        $targetMonth = $month ? date('Y-m', strtotime($month)) : now()->format('Y-m');

        $reviews = MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)
            ->whereYear('completed_at', date('Y', strtotime($targetMonth)))
            ->whereMonth('completed_at', date('m', strtotime($targetMonth)))
            ->with('participant', 'careManager', 'completedBy')
            ->get();

        return [
            'month' => $targetMonth,
            'total_completed' => $reviews->count(),
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'participant' => $review->participant->first_name.' '.$review->participant->last_name,
                    'care_manager' => $review->careManager->name,
                    'completed_by' => $review->completedBy?->name,
                    'completed_date' => $review->completed_at?->toDateString(),
                    'review_type' => $review->review_type,
                    'has_concerns' => ! empty($review->concerns),
                    'has_actions' => ! empty($review->actions_required),
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get outstanding reviews report
     */
    public function getOutstandingReviewsReport(): array
    {
        $due = $this->careReviewService->getAllDueReviews();
        $overdue = $this->careReviewService->getOverdueReviews();

        return [
            'total_outstanding' => $due->count() + $overdue->count(),
            'due' => [
                'count' => $due->count(),
                'reviews' => $due->map(function ($review) {
                    return [
                        'participant' => $review->participant->first_name.' '.$review->participant->last_name,
                        'care_manager' => $review->careManager->name,
                        'due_date' => $review->next_review_date->toDateString(),
                        'days_until_due' => $review->daysUntilDue(),
                    ];
                })->values()->toArray(),
            ],
            'overdue' => [
                'count' => $overdue->count(),
                'reviews' => $overdue->map(function ($review) {
                    return [
                        'participant' => $review->participant->first_name.' '.$review->participant->last_name,
                        'care_manager' => $review->careManager->name,
                        'due_date' => $review->next_review_date->toDateString(),
                        'days_overdue' => $review->daysOverdue(),
                    ];
                })->values()->toArray(),
            ],
        ];
    }

    /**
     * Get care manager workload
     */
    public function getCareManagerWorkload(): array
    {
        $managers = User::where('role', 'care_manager')
            ->with(['managedReviews' => function ($query) {
                $query->with('participant');
            }])
            ->get();

        return $managers->map(function ($manager) {
            $reviews = $manager->managedReviews;
            $dueCount = $reviews->where('status', ReviewStatus::DUE->value)->count();
            $overdueCount = $reviews->where('status', ReviewStatus::OVERDUE->value)->count();

            return [
                'care_manager' => $manager->name,
                'total_participants' => $reviews->distinct('participant_id')->count(),
                'reviews_due' => $dueCount,
                'reviews_overdue' => $overdueCount,
                'reviews_completed' => $reviews->where('status', ReviewStatus::COMPLETED->value)->count(),
                'workload_priority' => $dueCount + ($overdueCount * 2), // Weighting for priority
            ];
        })->sortByDesc('workload_priority')->values()->toArray();
    }

    /**
     * Get participant review history
     */
    public function getParticipantReviewHistory($participantId): array
    {
        $reviews = MonthlyCareReview::forParticipant($participantId)
            ->with('careManager', 'completedBy')
            ->orderByDesc('completed_at')
            ->get();

        return [
            'total_reviews' => $reviews->count(),
            'completed' => $reviews->where('status', ReviewStatus::COMPLETED->value)->count(),
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'type' => $review->review_type,
                    'status' => $review->status,
                    'completed_date' => $review->completed_at?->toDateString(),
                    'completed_by' => $review->completedBy?->name,
                    'care_manager' => $review->careManager->name,
                    'has_concerns' => ! empty($review->concerns),
                    'has_actions' => ! empty($review->actions_required),
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Export monthly review report to CSV
     */
    public function exportMonthlyReportAsCSV(string $month = ''): string
    {
        $report = $this->getMonthlyCompletionReport($month);

        $csv = "Participant,Care Manager,Completed By,Completed Date,Review Type,Has Concerns,Has Actions\n";

        foreach ($report['reviews'] as $review) {
            $csv .= implode(',', [
                '"'.$review['participant'].'"',
                '"'.$review['care_manager'].'"',
                '"'.($review['completed_by'] ?? '').'"',
                $review['completed_date'],
                $review['review_type'],
                $review['has_concerns'] ? 'Yes' : 'No',
                $review['has_actions'] ? 'Yes' : 'No',
            ])."\n";
        }

        return $csv;
    }

    /**
     * Export outstanding reviews to CSV
     */
    public function exportOutstandingReviewsAsCSV(): string
    {
        $report = $this->getOutstandingReviewsReport();

        $csv = "Participant,Care Manager,Status,Due Date,Days\n";

        foreach ($report['due']['reviews'] as $review) {
            $csv .= implode(',', [
                '"'.$review['participant'].'"',
                '"'.$review['care_manager'].'"',
                'Due',
                $review['due_date'],
                $review['days_until_due'],
            ])."\n";
        }

        foreach ($report['overdue']['reviews'] as $review) {
            $csv .= implode(',', [
                '"'.$review['participant'].'"',
                '"'.$review['care_manager'].'"',
                'Overdue',
                $review['due_date'],
                $review['days_overdue'],
            ])."\n";
        }

        return $csv;
    }
}
