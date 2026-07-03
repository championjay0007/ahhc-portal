<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use SoftDeletes;

    protected $table = 'support_tickets';

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'attachment_path',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: Ticket belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Ticket can have many support responses
     */
    public function responses()
    {
        return $this->hasMany(SupportResponse::class);
    }

    /**
     * Get admin who resolved this ticket
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope: Open tickets only
     */
    public function scopeOpen($query)
    {
        return $query->where('status', '!=', 'resolved')->where('status', '!=', 'closed');
    }

    /**
     * Scope: For participant user type
     */
    public function scopeForParticipants($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('role', 'participant');
        });
    }

    /**
     * Check if ticket is open
     */
    public function isOpen()
    {
        return $this->status !== 'resolved' && $this->status !== 'closed';
    }

    /**
     * Mark as resolved
     */
    public function markResolved($adminId)
    {
        $this->status = 'resolved';
        $this->resolved_by = $adminId;
        $this->resolved_at = now();
        $this->save();
    }
}
