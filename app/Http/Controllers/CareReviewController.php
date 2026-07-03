<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\MonthlyCareReview;
use App\Models\Participant;
use App\Models\User;
use App\Services\CareReviewDashboardService;
use App\Services\CareReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareReviewController extends Controller
{
    public function __construct(
        private CareReviewService $reviewService,
        private CareReviewDashboardService $dashboardService
    ) {}

    /**
     * Get all care reviews
     */
    public function index(Request $request): JsonResponse
    {
        $query = MonthlyCareReview::with('participant', 'careManager', 'completedBy');

        if ($request->has('participant_id')) {
            $query->where('participant_id', $request->participant_id);
        }

        if ($request->has('care_manager_id')) {
            $query->where('care_manager_id', $request->care_manager_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('review_type')) {
            $query->where('review_type', $request->review_type);
        }

        $reviews = $query->orderByDesc('next_review_date')->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $reviews->items(),
            'links' => [
                'first' => $reviews->url(1),
                'last' => $reviews->url($reviews->lastPage()),
                'prev' => $reviews->previousPageUrl(),
                'next' => $reviews->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'from' => $reviews->firstItem(),
                'last_page' => $reviews->lastPage(),
                'path' => $reviews->path(),
                'per_page' => $reviews->perPage(),
                'to' => $reviews->lastItem(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Get a specific review
     */
    public function show(MonthlyCareReview $review): JsonResponse
    {
        $review->load('participant', 'careManager', 'completedBy', 'activities', 'contactLogs');

        return response()->json($review);
    }

    /**
     * Create a new review
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'care_manager_id' => 'required|exists:users,id',
            'review_type' => 'required|string',
            'next_review_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $participant = Participant::findOrFail($validated['participant_id']);
        $careManager = User::findOrFail($validated['care_manager_id']);

        $review = $this->reviewService->createReview(
            $participant,
            $careManager,
            $validated['review_type'],
            $validated['next_review_date'] ?? null
        );

        if (isset($validated['notes'])) {
            $review->update(['notes' => $validated['notes']]);
        }

        return response()->json($review, 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, MonthlyCareReview $review): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'concerns' => 'nullable|string',
            'actions_required' => 'nullable|string',
            'next_review_date' => 'nullable|date',
        ]);

        if (isset($validated['concerns'])) {
            $this->reviewService->addConcerns($review, $validated['concerns']);
        }

        if (isset($validated['actions_required'])) {
            $this->reviewService->addActionsRequired($review, $validated['actions_required']);
        }

        if (isset($validated['notes'])) {
            $review->update(['notes' => $validated['notes']]);
        }

        if (isset($validated['next_review_date'])) {
            $this->reviewService->scheduleNextReview($review, $validated['next_review_date']);
        }

        return response()->json($review->fresh(['participant', 'careManager']));
    }

    /**
     * Complete a review
     */
    public function complete(Request $request, MonthlyCareReview $review): JsonResponse
    {
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
            'next_review_date' => 'nullable|date',
        ]);

        $review = $this->reviewService->completeReview(
            $review,
            auth()->user(),
            $validated['completion_notes'] ?? '',
            $validated['next_review_date'] ?? ''
        );

        return response()->json([
            'message' => 'Review completed successfully',
            'review' => $review->fresh(['participant', 'careManager', 'completedBy']),
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard(): JsonResponse
    {
        $stats = $this->dashboardService->getDashboardStats();

        return response()->json($stats);
    }

    /**
     * Get reviews due
     */
    public function reviewsDue(): JsonResponse
    {
        $data = $this->dashboardService->getReviewsDue();

        return response()->json($data);
    }

    /**
     * Get reviews completed
     */
    public function reviewsCompleted(): JsonResponse
    {
        $data = $this->dashboardService->getReviewsCompleted();

        return response()->json($data);
    }

    /**
     * Get overdue reviews
     */
    public function reviewsOverdue(): JsonResponse
    {
        $data = $this->dashboardService->getReviewsOverdue();

        return response()->json($data);
    }

    /**
     * Get outstanding reviews report
     */
    public function outstandingReport(): JsonResponse
    {
        $report = $this->dashboardService->getOutstandingReviewsReport();

        return response()->json($report);
    }

    /**
     * Get monthly completion report
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $month = $request->get('month');
        $report = $this->dashboardService->getMonthlyCompletionReport($month);

        return response()->json($report);
    }

    /**
     * Get care manager workload
     */
    public function careManagerWorkload(): JsonResponse
    {
        $workload = $this->dashboardService->getCareManagerWorkload();

        return response()->json($workload);
    }

    /**
     * Export monthly report
     */
    public function exportMonthlyReport(Request $request)
    {
        $month = $request->get('month');
        $csv = $this->dashboardService->exportMonthlyReportAsCSV($month);
        $filename = 'care-review-report-'.($month ?? now()->format('Y-m')).'.csv';

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export outstanding reviews report
     */
    public function exportOutstandingReport()
    {
        $csv = $this->dashboardService->exportOutstandingReviewsAsCSV();
        $filename = 'outstanding-reviews-'.now()->format('Y-m-d').'.csv';

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get participant review history
     */
    public function participantHistory(Participant $participant): JsonResponse
    {
        $history = $this->dashboardService->getParticipantReviewHistory($participant->id);

        return response()->json($history);
    }

    /**
     * Get review activity log
     */
    public function activityLog(MonthlyCareReview $review): JsonResponse
    {
        $activities = $this->reviewService->getReviewActivityLog($review);

        return response()->json([
            'review_id' => $review->id,
            'activity_count' => $activities->count(),
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->activity_type,
                    'description' => $activity->description,
                    'user' => $activity->user?->name,
                    'created_at' => $activity->created_at->toDateTimeString(),
                ];
            }),
        ]);
    }

    /**
     * Get review types
     */
    public function getReviewTypes(): JsonResponse
    {
        return response()->json(ReviewType::options());
    }

    /**
     * Get review statuses
     */
    public function getReviewStatuses(): JsonResponse
    {
        return response()->json(ReviewStatus::options());
    }
}
