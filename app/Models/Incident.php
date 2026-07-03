<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'participant_id',
        'worker_id',
        'shift_id',
        'reported_by_id',
        'incident_type',
        'type',
        'severity',
        'description',
        'location',
        'occurred_at',
        'action_taken',
        'status',
        'follow_up_required',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'follow_up_required' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getTypeAttribute()
    {
        return $this->incident_type;
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['incident_type'] = $value;
    }
}
