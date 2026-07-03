<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreApprovalRequest extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_APPROVED_WITH_CONDITIONS = 'approved_with_conditions';

    public const STATUS_INFO_REQUESTED = 'info_requested';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_APPROVED,
            self::STATUS_APPROVED_WITH_CONDITIONS,
            self::STATUS_INFO_REQUESTED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_EXPIRED,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return ucfirst(str_replace('_', ' ', $status));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    protected $fillable = [
        'participant_id',
        'support_person_id',
        'worker_id',
        'supplier_id',
        'request_number',
        'service_type',
        'service_category',
        'purpose',
        'description',
        'requested_amount_cents',
        'estimated_amount_cents',
        'committed_amount_cents',
        'start_date',
        'end_date',
        'expiry_date',
        'quote_file_path',
        'status',
        'admin_id',
        'decision_reason',
        'submitted_at',
        'approved_at',
        'approved_by_id',
        'notes',
        'review_notes',
    ];

    protected $casts = [
        'requested_amount_cents' => 'integer',
        'estimated_amount_cents' => 'integer',
        'committed_amount_cents' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'expiry_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PreApprovalAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PreApprovalComment::class)->orderBy('created_at', 'desc');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'supplier_id');
    }

    public function supportPerson(): BelongsTo
    {
        return $this->belongsTo(SupportPerson::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'pre_approval_id');
    }
}
