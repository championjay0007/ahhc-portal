<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyCareReview extends Model
{
    protected $fillable = [
        'participant_id',
        'care_manager_id',
        'review_date',
        'review_type',
        'notes',
        'concerns',
        'actions_required',
        'next_review_date',
        'status',
        'completed_at',
        'completed_by_id',
        'completion_notes',
        'due_date_reminder_sent_at',
        'today_reminder_sent_at',
        'overdue_reminder_sent_at',
    ];

    protected $casts = [
        'review_date' => 'date',
        'next_review_date' => 'date',
        'completed_at' => 'datetime',
        'due_date_reminder_sent_at' => 'datetime',
        'today_reminder_sent_at' => 'datetime',
        'overdue_reminder_sent_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function careManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'care_manager_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CareReviewActivity::class, 'monthly_care_review_id');
    }

    public function contactLogs(): HasMany
    {
        return $this->hasMany(CareContactLog::class, 'monthly_care_review_id');
    }

    /**
     * Get the review type enum
     */
    public function getReviewTypeEnum(): ReviewType
    {
        return ReviewType::from($this->review_type);
    }

    /**
     * Get the status enum
     */
    public function getStatusEnum(): ReviewStatus
    {
        return ReviewStatus::from($this->status);
    }

    /**
     * Check if review is due
     */
    public function isDue(): bool
    {
        if ($this->status !== ReviewStatus::DUE->value || ! $this->next_review_date) {
            return false;
        }

        return $this->next_review_date->gte(now()->startOfDay());
    }

    /**
     * Check if review is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->next_review_date) {
            return false;
        }

        return $this->status === ReviewStatus::OVERDUE->value
            || ($this->status === ReviewStatus::DUE->value && $this->next_review_date->lt(now()->startOfDay()));
    }

    /**
     * Check if review is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === ReviewStatus::COMPLETED->value;
    }

    /**
     * Get days until review due
     */
    public function daysUntilDue(): ?int
    {
        if (! $this->next_review_date) {
            return null;
        }

        $today = now()->startOfDay();

        if ($this->next_review_date->lt($today)) {
            return null;
        }

        return $today->diffInDays($this->next_review_date);
    }

    /**
     * Get days overdue
     */
    public function daysOverdue(): ?int
    {
        if (! $this->next_review_date) {
            return null;
        }

        $today = now()->startOfDay();

        if ($this->next_review_date->gte($today)) {
            return null;
        }

        return $this->next_review_date->diffInDays($today);
    }

    /**
     * Mark review as completed
     */
    public function markAsCompleted(User $completedBy, string $notes = ''): void
    {
        $this->update([
            'status' => ReviewStatus::COMPLETED->value,
            'completed_at' => now(),
            'completed_by_id' => $completedBy->id,
            'completion_notes' => $notes,
            'review_date' => now()->toDateString(),
        ]);

        // Log the activity
        $this->activities()->create([
            'user_id' => $completedBy->id,
            'activity_type' => 'completed',
            'description' => 'Review marked as completed',
        ]);
    }

    /**
     * Schedule next review
     */
    public function scheduleNextReview(string $nextReviewDate): void
    {
        $this->update(['next_review_date' => $nextReviewDate]);

        $this->activities()->create([
            'user_id' => auth()->id(),
            'activity_type' => 'updated',
            'description' => "Next review scheduled for {$nextReviewDate}",
        ]);
    }

    /**
     * Add concerns
     */
    public function addConcerns(string $concerns): void
    {
        $existingConcerns = $this->concerns ? $this->concerns."\n\n---\n" : '';
        $this->update(['concerns' => $existingConcerns.$concerns]);

        $this->activities()->create([
            'user_id' => auth()->id(),
            'activity_type' => 'updated',
            'description' => 'Concerns added to review',
        ]);
    }

    /**
     * Add actions required
     */
    public function addActionsRequired(string $actions): void
    {
        $existingActions = $this->actions_required ? $this->actions_required."\n\n---\n" : '';
        $this->update(['actions_required' => $existingActions.$actions]);

        $this->activities()->create([
            'user_id' => auth()->id(),
            'activity_type' => 'updated',
            'description' => 'Actions required added to review',
        ]);
    }

    /**
     * Scope queries to due reviews
     */
    public function scopeDue($query)
    {
        return $query->where('status', ReviewStatus::DUE->value);
    }

    /**
     * Scope queries to completed reviews
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', ReviewStatus::COMPLETED->value);
    }

    /**
     * Scope queries to overdue reviews
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', ReviewStatus::OVERDUE->value);
    }

    /**
     * Scope queries to in-progress reviews
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', ReviewStatus::IN_PROGRESS->value);
    }

    /**
     * Scope queries by participant
     */
    public function scopeForParticipant($query, $participantId)
    {
        return $query->where('participant_id', $participantId);
    }

    /**
     * Scope queries by care manager
     */
    public function scopeForCareManager($query, $careManagerId)
    {
        return $query->where('care_manager_id', $careManagerId);
    }

    /**
     * Scope queries by review type
     */
    public function scopeOfType($query, $reviewType)
    {
        return $query->where('review_type', $reviewType);
    }

    /**
     * Scope queries by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('review_date', [$startDate, $endDate]);
    }
}
