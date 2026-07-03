<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalNotification extends Model
{
    protected $table = 'portal_notifications';

    protected $fillable = [
        'user_id',
        'recipient_id',
        'participant_id',
        'worker_id',
        'title',
        'message',
        'channel',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}
