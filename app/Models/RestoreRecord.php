<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestoreRecord extends Model
{
    public const STATUS_SUCCESSFUL = 'Successful';

    public const STATUS_FAILED = 'Failed';

    protected $fillable = [
        'backup_record_id',
        'restore_date',
        'status',
        'initiated_by_id',
        'notes',
    ];

    protected $casts = [
        'restore_date' => 'datetime',
    ];

    public function backupRecord(): BelongsTo
    {
        return $this->belongsTo(BackupRecord::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }
}
