<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentNote extends Model
{
    protected $fillable = [
        'assessment_id',
        'created_by_user_id',
        'note_text',
        'note_type',
        'is_internal',
        'requires_action',
        'ip_address',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'requires_action' => 'boolean',
    ];

    const NOTE_TYPE_GENERAL = 'general';

    const NOTE_TYPE_ELIGIBILITY = 'eligibility';

    const NOTE_TYPE_SUITABILITY = 'suitability';

    const NOTE_TYPE_FUNDING = 'funding';

    const NOTE_TYPE_DECISION = 'decision';

    const NOTE_TYPE_INFORMATION_REQUEST = 'information_request';

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get note type label
     */
    public static function getNoteTypeLabel(string $type): string
    {
        $labels = [
            self::NOTE_TYPE_GENERAL => 'General Note',
            self::NOTE_TYPE_ELIGIBILITY => 'Eligibility Assessment',
            self::NOTE_TYPE_SUITABILITY => 'Suitability Assessment',
            self::NOTE_TYPE_FUNDING => 'Funding Assessment',
            self::NOTE_TYPE_DECISION => 'Decision Note',
            self::NOTE_TYPE_INFORMATION_REQUEST => 'Information Request',
        ];

        return $labels[$type] ?? 'Note';
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeRequiresAction($query)
    {
        return $query->where('requires_action', true);
    }
}
