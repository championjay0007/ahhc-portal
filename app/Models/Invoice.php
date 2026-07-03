<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'participant_id',
        'pre_approval_id',
        'worker_id',
        'shift_id',
        'invoice_number',
        'status',
        'amount_cents',
        'invoice_date',
        'service_date',
        'due_date',
        'invoice_file_path',
        'paid_at',
        'approved_at',
        'approved_by_id',
        'notes',
        'attachment_path',
        'attachment_disk',
        'attachment_mime_type',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'invoice_date' => 'date',
        'service_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function preApprovalRequest(): BelongsTo
    {
        return $this->belongsTo(PreApprovalRequest::class, 'pre_approval_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function careNotes()
    {
        return CareNote::query()
            ->where('participant_id', $this->participant_id)
            ->when($this->service_date, fn ($query) => $query->whereDate('shift_date', $this->service_date));
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
