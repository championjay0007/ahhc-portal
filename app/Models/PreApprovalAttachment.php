<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreApprovalAttachment extends Model
{
    protected $fillable = [
        'pre_approval_request_id',
        'uploaded_by_id',
        'title',
        'file_path',
        'mime_type',
        'size_bytes',
        'notes',
    ];

    public function preApprovalRequest(): BelongsTo
    {
        return $this->belongsTo(PreApprovalRequest::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
