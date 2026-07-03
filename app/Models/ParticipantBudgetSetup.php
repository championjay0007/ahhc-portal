<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipantBudgetSetup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assessment_id',
        'participant_id',
        'configured_by_user_id',
        'financial_year',
        'quarter',
        'quarter_start_date',
        'quarter_end_date',
        'opening_budget',
        'carry_over_amount',
        'total_available_budget',
        'approved_invoices_total',
        'pending_invoices_total',
        'remaining_budget',
        'budget_categories',
        'status',
        'is_current',
        'setup_notes',
    ];

    protected $casts = [
        'financial_year' => 'integer',
        'quarter' => 'integer',
        'quarter_start_date' => 'date',
        'quarter_end_date' => 'date',
        'opening_budget' => 'decimal:2',
        'carry_over_amount' => 'decimal:2',
        'total_available_budget' => 'decimal:2',
        'approved_invoices_total' => 'decimal:2',
        'pending_invoices_total' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'budget_categories' => 'array',
        'is_current' => 'boolean',
    ];

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_SUPERSEDED = 'superseded';

    const QUARTER_Q1 = 1;

    const QUARTER_Q2 = 2;

    const QUARTER_Q3 = 3;

    const QUARTER_Q4 = 4;

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function configuredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'configured_by_user_id');
    }

    /**
     * Get quarter label
     */
    public function getQuarterLabel(): string
    {
        return "Q{$this->quarter} {$this->financial_year}";
    }

    /**
     * Calculate remaining budget
     */
    public function calculateRemainingBudget(): void
    {
        $this->remaining_budget = $this->total_available_budget - $this->approved_invoices_total - $this->pending_invoices_total;
        $this->save();
    }

    /**
     * Get percentage of budget used
     */
    public function getUsagePercentage(): float
    {
        if ($this->total_available_budget == 0) {
            return 0;
        }

        return ($this->approved_invoices_total / $this->total_available_budget) * 100;
    }

    /**
     * Check if budget is exhausted
     */
    public function isBudgetExhausted(): bool
    {
        return $this->remaining_budget <= 0;
    }

    /**
     * Check if budget is running low (80%+)
     */
    public function isBudgetLow(): bool
    {
        return $this->getUsagePercentage() >= 80;
    }

    /**
     * Get budget category allocation
     */
    public function getBudgetCategoryAllocation(string $category): ?float
    {
        if (! $this->budget_categories) {
            return null;
        }

        return $this->budget_categories[$category] ?? null;
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForFinancialYear($query, int $year)
    {
        return $query->where('financial_year', $year);
    }

    public function scopeForQuarter($query, int $quarter)
    {
        return $query->where('quarter', $quarter);
    }
}
