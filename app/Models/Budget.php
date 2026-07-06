<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Budget extends Model
{
    protected $fillable = [
        'participant_id', 'quarter_start', 'quarter_end', 'opening_budget', 'carry_over',
        'total_available', 'committed_funds', 'pending_invoices', 'approved_spend', 'paid_spend', 'remaining_balance',
        // legacy/compat columns
        'quarter_start_date', 'quarter_end_date',
        'opening_balance_cents', 'carry_over_cents', 'committed_cents', 'approved_spend_cents', 'paid_spend_cents',
    ];

    protected $casts = [
        'quarter_start' => 'date',
        'quarter_end' => 'date',
        'quarter_start_date' => 'date',
        'quarter_end_date' => 'date',
        'opening_budget' => 'decimal:2',
        'carry_over' => 'decimal:2',
        'total_available' => 'decimal:2',
        'committed_funds' => 'decimal:2',
        'pending_invoices' => 'decimal:2',
        'approved_spend' => 'decimal:2',
        'paid_spend' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function categories()
    {
        return $this->belongsToMany(BudgetCategory::class, 'budget_transactions', 'budget_id', 'category_id')->distinct();
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    // Compatibility accessors/mutators to support both decimal fields and legacy cents fields
    protected function hasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn($this->getTable(), $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getCentsField(string $field): ?string
    {
        $map = [
            'opening_budget' => 'opening_balance_cents',
            'carry_over' => 'carry_over_cents',
            'committed_funds' => 'committed_cents',
            'approved_spend' => 'approved_spend_cents',
            'paid_spend' => 'paid_spend_cents',
        ];

        return $map[$field] ?? null;
    }

    public function getAttributeValue($key)
    {
        // If actual attribute exists, return default behaviour
        if (array_key_exists($key, $this->attributes) || $this->hasColumn($key)) {
            return parent::getAttributeValue($key);
        }

        // Map legacy date fields to logical quarter fields
        if ($key === 'quarter_start' && $this->hasColumn('quarter_start_date')) {
            return parent::getAttributeValue('quarter_start_date');
        }

        if ($key === 'quarter_end' && $this->hasColumn('quarter_end_date')) {
            return parent::getAttributeValue('quarter_end_date');
        }

        // Map decimal logical fields to cents if present
        $cents = $this->getCentsField($key);
        if ($cents && $this->hasColumn($cents)) {
            $val = parent::getAttributeValue($cents);

            return is_null($val) ? null : ($val / 100);
        }

        // Derived fields
        if ($key === 'total_available') {
            $opening = $this->getAttributeValue('opening_budget');
            $carry = $this->getAttributeValue('carry_over');

            return ($opening ?? 0) + ($carry ?? 0);
        }

        if ($key === 'remaining_balance') {
            $total = $this->getAttributeValue('total_available');
            $committed = $this->getAttributeValue('committed_funds') ?? 0;
            $approved = $this->getAttributeValue('approved_spend') ?? 0;
            $paid = $this->getAttributeValue('paid_spend') ?? 0;

            $usedMode = config('budget.used_mode', 'approved');
            $used = $usedMode === 'paid' ? $paid : $approved;

            return $total - $committed - $used;
        }

        return parent::getAttributeValue($key);
    }

    public function setAttribute($key, $value)
    {
        // Map logical quarter date fields to legacy date column names when necessary
        if ($key === 'quarter_start' && $this->hasColumn('quarter_start_date')) {
            return parent::setAttribute('quarter_start_date', $value);
        }

        if ($key === 'quarter_end' && $this->hasColumn('quarter_end_date')) {
            return parent::setAttribute('quarter_end_date', $value);
        }

        // If underlying column exists, use default behaviour
        if ($this->hasColumn($key)) {
            return parent::setAttribute($key, $value);
        }
        // If underlying column exists, use default behaviour
        if ($this->hasColumn($key)) {
            return parent::setAttribute($key, $value);
        }

        // If there is a cents field, set it and do NOT set the non-existent decimal column
        $cents = $this->getCentsField($key);
        if ($cents && $this->hasColumn($cents)) {
            $int = is_null($value) ? null : (int) round($value * 100);

            return parent::setAttribute($cents, $int);
        }

        // If date-field logical names are used on legacy schema, map them to the legacy columns.
        if ($key === 'quarter_start' && $this->hasColumn('quarter_start_date')) {
            return parent::setAttribute('quarter_start_date', $value);
        }

        if ($key === 'quarter_end' && $this->hasColumn('quarter_end_date')) {
            return parent::setAttribute('quarter_end_date', $value);
        }

        // For other non-column attributes, ignore setting to avoid SQL errors on insert
        return $this;
    }
}
