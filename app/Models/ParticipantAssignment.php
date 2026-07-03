<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantAssignment extends Model
{
    protected $fillable = [
        'participant_id',
        'worker_id',
        'support_person_id',
        'start_date',
        'end_date',
        'assignment_type',
        'status',
        'is_primary',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_primary' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function supportPerson(): BelongsTo
    {
        return $this->belongsTo(SupportPerson::class);
    }
}
