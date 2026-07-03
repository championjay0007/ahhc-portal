<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupRecord extends Model
{
    public const TYPE_DATABASE = 'database';

    public const TYPE_FILE_STORAGE = 'file_storage';

    public const TYPE_AUDIT_LOG = 'audit_log';

    public const STATUS_SUCCESSFUL = 'Successful';

    public const STATUS_FAILED = 'Failed';

    public const STATUS_IN_PROGRESS = 'In Progress';

    protected $fillable = [
        'backup_type',
        'backup_date',
        'size',
        'status',
        'storage_location',
        'notes',
    ];

    protected $casts = [
        'backup_date' => 'datetime',
        'size' => 'integer',
    ];

    public function restores(): HasMany
    {
        return $this->hasMany(RestoreRecord::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESSFUL);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
}
