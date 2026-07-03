<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'channel_email',
        'channel_in_app',
        'channel_push',
        'channel_sms',
        'events',
    ];

    protected $casts = [
        'channel_email' => 'boolean',
        'channel_in_app' => 'boolean',
        'channel_push' => 'boolean',
        'channel_sms' => 'boolean',
        'events' => 'array',
    ];
}
