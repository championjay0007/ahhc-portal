<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareContactLog extends Model
{
    protected $table = 'care_contact_logs';

    protected $fillable = [
        'participant_id',
        'care_manager_id',
        'monthly_care_review_id',
        'contact_datetime',
        'contact_type',
        'contact_method',
        'notes',
        'outcomes',
        'follow_up_required',
        'follow_up_date',
    ];

    protected $casts = [
        'contact_datetime' => 'datetime',
        'follow_up_date' => 'date',
        'follow_up_required' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function careManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'care_manager_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(MonthlyCareReview::class, 'monthly_care_review_id');
    }

    public function scopeForParticipant($query, $participantId)
    {
        return $query->where('participant_id', $participantId);
    }

    public function scopeForCareManager($query, $careManagerId)
    {
        return $query->where('care_manager_id', $careManagerId);
    }

    public function scopeWithFollowUp($query)
    {
        return $query->where('follow_up_required', true);
    }

    public function scopeByContactType($query, $contactType)
    {
        return $query->where('contact_type', $contactType);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderByDesc('contact_datetime');
    }
}
