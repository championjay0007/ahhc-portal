<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareReviewActivity extends Model
{
    protected $fillable = [
        'monthly_care_review_id',
        'user_id',
        'activity_type',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(MonthlyCareReview::class, 'monthly_care_review_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForReview($query, $reviewId)
    {
        return $query->where('monthly_care_review_id', $reviewId);
    }

    public function scopeOfType($query, $activityType)
    {
        return $query->where('activity_type', $activityType);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
