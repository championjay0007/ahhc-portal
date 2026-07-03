<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentStatusHistory extends Model
{
    protected $table = 'assessment_status_history';

    protected $fillable = [
        'assessment_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'transition_reason',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Get human-readable transition description
     */
    public function getTransitionDescription(): string
    {
        $from = Assessment::getStatusLabel($this->from_status);
        $to = Assessment::getStatusLabel($this->to_status);

        return "{$from} → {$to}";
    }

    /**
     * Get who made the change
     */
    public function getChangedByName(): string
    {
        return $this->changedByUser?->name ?? 'System';
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
