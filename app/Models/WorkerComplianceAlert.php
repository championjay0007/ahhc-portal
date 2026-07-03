<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerComplianceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'worker_compliance_document_id',
        'alert_type',
        'alert_level',
        'document_type',
        'message',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(WorkerComplianceDocument::class, 'worker_compliance_document_id');
    }
}
