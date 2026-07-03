<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SignatureRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VIEWED = 'viewed';

    public const STATUS_SIGNED = 'signed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'document_id',
        'assigned_user_id',
        'assigned_by',
        'status',
        'assigned_at',
        'completed_at',
        'expires_at',
        'reminder_sent_at_3d',
        'reminder_sent_at_7d',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'reminder_sent_at_3d' => 'datetime',
        'reminder_sent_at_7d' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function signature(): HasOne
    {
        return $this->hasOne(DocumentSignature::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_VIEWED]);
    }

    public function markViewed(): static
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->status = self::STATUS_VIEWED;
        }

        if (! $this->assigned_at) {
            $this->assigned_at = now();
        }

        $this->save();

        return $this;
    }

    public function markSigned(): static
    {
        $this->status = self::STATUS_SIGNED;
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    public function markExpired(): static
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();

        return $this;
    }
}
