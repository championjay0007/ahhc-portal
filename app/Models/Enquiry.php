<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'New';

    public const STATUS_CONTACTED = 'Contacted';

    public const STATUS_ASSESSMENT_SCHEDULED = 'Assessment Scheduled';

    public const STATUS_APPROVED = 'Approved';

    public const STATUS_NOT_SUITABLE = 'Not Suitable';

    public const STATUS_CLOSED = 'Closed';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'support_at_home_status',
        'message',
        'consent',
        'status',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'consent' => 'boolean',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_ASSESSMENT_SCHEDULED,
            self::STATUS_APPROVED,
            self::STATUS_NOT_SUITABLE,
            self::STATUS_CLOSED,
        ];
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
