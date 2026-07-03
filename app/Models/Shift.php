<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'participant_id',
        'worker_id',
        'service_type',
        'service_category',
        'shift_date',
        'start_time',
        'end_time',
        'location',
        'notes',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_MISSED = 'missed';

    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_CONFIRMED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_MISSED,
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function careNotes(): HasMany
    {
        return $this->hasMany(CareNote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
