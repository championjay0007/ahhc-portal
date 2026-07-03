<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BudgetTransaction extends Model
{
    const TYPE_OPENING = 'opening_balance';

    const TYPE_CARRY = 'carry_over';

    const TYPE_COMMIT = 'commitment';

    const TYPE_PENDING = 'invoice_pending';

    const TYPE_APPROVED = 'invoice_approved';

    const TYPE_PAID = 'invoice_paid';

    const TYPE_RELEASE = 'release_commitment';

    const TYPE_ADJUST = 'adjustment';

    protected $fillable = ['budget_id', 'type', 'category_id', 'amount', 'meta', 'created_by'];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    protected function hasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn($this->getTable(), $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAttributeValue($key)
    {
        if (array_key_exists($key, $this->attributes) || $this->hasColumn($key)) {
            return parent::getAttributeValue($key);
        }

        if ($key === 'amount' && $this->hasColumn('amount_cents')) {
            $val = parent::getAttributeValue('amount_cents');

            return is_null($val) ? null : ($val / 100);
        }

        return parent::getAttributeValue($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasColumn($key)) {
            return parent::setAttribute($key, $value);
        }

        if ($key === 'amount' && $this->hasColumn('amount_cents')) {
            $int = is_null($value) ? null : (int) round($value * 100);

            return parent::setAttribute('amount_cents', $int);
        }

        // If column doesn't exist (eg. category_id), ignore to avoid SQL errors
        return $this;
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }
}
