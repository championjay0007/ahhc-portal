<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportResponse extends Model
{
    protected $table = 'support_responses';

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Response belongs to a ticket
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * Response belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
