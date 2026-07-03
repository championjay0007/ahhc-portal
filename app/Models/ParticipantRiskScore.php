<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantRiskScore extends Model
{
    protected $fillable = [
        'participant_id',
        'score',
        'level',
        'trigger_reasons',
        'score_breakdown',
        'calculated_by_id',
        'calculated_at',
    ];

    protected $casts = [
        'trigger_reasons' => 'array',
        'score_breakdown' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
