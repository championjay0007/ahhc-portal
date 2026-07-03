<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportMessage extends Model
{
    use SoftDeletes;

    protected $table = 'support_messages';

    protected $fillable = [
        'support_conversation_id',
        'user_id',
        'message',
        'is_admin',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Message belongs to a conversation
     */
    public function conversation()
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    /**
     * Message belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}
