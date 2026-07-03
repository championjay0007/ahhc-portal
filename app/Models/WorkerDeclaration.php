<?php

namespace App\Models;

use App\Enums\WorkerDeclarationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerDeclaration extends Model
{
    protected $fillable = [
        'worker_id',
        'declaration_type',
        'declaration_text',
        'agreed',
        'signed_at',
        'signature_file_path',
        'declined_at',
        'decline_reason',
    ];

    protected $casts = [
        'agreed' => 'boolean',
        'signed_at' => 'datetime',
        'declined_at' => 'datetime',
        'declaration_type' => WorkerDeclarationType::class,
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function isSigned(): bool
    {
        return $this->agreed && $this->signed_at !== null;
    }

    public function isDeclined(): bool
    {
        return $this->declined_at !== null;
    }
}
