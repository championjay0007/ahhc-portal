<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareNote extends Model
{
    protected $fillable = [
        'participant_id',
        'worker_id',
        'shift_id',
        'shift_date',
        'start_time',
        'end_time',
        'tasks_completed',
        'care_summary',
        'observations',
        'risks_flag',
        'attachment_path',
        'service_confirmed',
        'service_type',
        'status',
        'submitted_at',
        'created_by_id',
        'approved_by_id',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'submitted_at' => 'datetime',
        'risks_flag' => 'boolean',
        'service_confirmed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $careNote): void {
            if (empty($careNote->care_summary) && ! empty($careNote->tasks_completed)) {
                $careNote->care_summary = $careNote->tasks_completed;
            }

            if (empty($careNote->care_summary) && ! empty($careNote->observations)) {
                $careNote->care_summary = $careNote->observations;
            }
        });
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
