<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportConversation extends Model
{
    use SoftDeletes;

    protected $table = 'support_conversations';

    protected $fillable = [
        'user_id',
        'admin_id',
        'subject',
        'submitted_name',
        'submitted_email',
        'status',
        'priority',
        'last_message_at',
        'resolved_at',
        'public_token',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Conversation belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Conversation belongs to an admin
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Conversation has many messages
     */
    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function markAdminMessagesAsRead()
    {
        $this->messages()->where('is_admin', true)->whereNull('read_at')->update(['read_at' => now()]);
    }

    /**
     * Get unread message count
     */
    public function unreadCount()
    {
        return $this->messages()->whereNull('read_at')->count();
    }

    /**
     * Mark all messages as read
     */
    public function markAsRead()
    {
        $this->messages()->whereNull('read_at')->update(['read_at' => now()]);
    }
}
