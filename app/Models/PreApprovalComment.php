<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreApprovalComment extends Model
{
    protected $fillable = [
        'pre_approval_request_id',
        'commented_by_id',
        'comment_type',
        'message',
    ];

    public function preApprovalRequest(): BelongsTo
    {
        return $this->belongsTo(PreApprovalRequest::class);
    }

    public function commenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commented_by_id');
    }
}
