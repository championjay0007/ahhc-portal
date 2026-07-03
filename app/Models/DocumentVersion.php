<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id',
        'storage_disk',
        'path',
        'mime_type',
        'size_bytes',
        'uploaded_by_id',
        'version_number',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'metadata' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function isPreviewable(): bool
    {
        return Document::isPreviewableMime($this->mime_type);
    }
}
