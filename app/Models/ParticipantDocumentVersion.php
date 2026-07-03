<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantDocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_document_id',
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

    public function participantDocument(): BelongsTo
    {
        return $this->belongsTo(ParticipantDocument::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
