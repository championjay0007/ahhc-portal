<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisasterRecoveryTest extends Model
{
    public const STATUS_PASSED = 'Passed';

    public const STATUS_FAILED = 'Failed';

    public const STATUS_PARTIAL = 'Partial';

    protected $fillable = [
        'test_date',
        'status',
        'conducted_by_id',
        'summary',
        'notes',
    ];

    protected $casts = [
        'test_date' => 'datetime',
    ];

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by_id');
    }
}
