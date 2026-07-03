<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParticipantApplication extends Model
{
    protected $table = 'participant_applications';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postcode',
        'disability_category',
        'support_needs',
        'funding_source',
        'status',
        'rejected_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'status' => ApplicationStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Relationship to the created participant (after approval)
     */
    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class, 'application_id');
    }

    /**
     * Get the reviewing admin user
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if application is approved
     */
    public function isApproved(): bool
    {
        return $this->status === ApplicationStatus::APPROVED;
    }

    /**
     * Check if application is pending
     */
    public function isPending(): bool
    {
        return $this->status === ApplicationStatus::NEW_APPLICATION;
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
