<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgreementSignature extends Model
{
    protected $table = 'agreement_signatures';

    protected $fillable = [
        'agreement_id',
        'participant_id',
        'signature_image',
        'signed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * Agreement being signed
     */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    /**
     * Participant who signed
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get the date/time formatted for display
     */
    public function getSignedAtFormattedAttribute(): string
    {
        return $this->signed_at?->format('d M Y H:i:s') ?? 'Not signed';
    }

    /**
     * Check if signature is valid (has image, timestamp, participant)
     */
    public function isValid(): bool
    {
        return ! empty($this->signature_image) &&
               $this->signed_at !== null &&
               $this->participant_id !== null;
    }
}
