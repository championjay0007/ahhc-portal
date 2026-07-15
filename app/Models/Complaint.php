<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'participant_id',
        'support_person_id',
        'submitted_by_id',
        'category',
        'priority',
        'description',
        'status',
        'received_at',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function supportPerson(): BelongsTo
    {
        return $this->belongsTo(SupportPerson::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->submitter();
    }

    public function getSubmittedByDisplayNameAttribute(): ?string
    {
        $submitter = $this->relationLoaded('submitter')
            ? $this->getRelation('submitter')
            : $this->submitter()->first();

        return $submitter?->name;
    }
}
