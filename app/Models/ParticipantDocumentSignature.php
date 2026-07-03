<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantDocumentSignature extends Model
{
    protected $table = 'participant_document_signatures';

    protected $fillable = [
        'participant_id',
        'document_id',
        'signed_at',
        'signature_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
