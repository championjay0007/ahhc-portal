<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerServiceApproval extends Model
{
    protected $fillable = [
        'worker_id',
        'service_category',
        'description',
        'approved_at',
        'approved_by_id',
        'status',
        'approval_start_date',
        'approval_end_date',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'approval_start_date' => 'date',
        'approval_end_date' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->approved_at !== null;
    }

    public function isExpired(): bool
    {
        if (! $this->approval_end_date) {
            return false;
        }

        return now()->isAfter($this->approval_end_date);
    }

    public function isActive(): bool
    {
        if (! $this->isApproved()) {
            return false;
        }

        if ($this->approval_start_date && now()->isBefore($this->approval_start_date)) {
            return false;
        }

        return ! $this->isExpired();
    }
}
