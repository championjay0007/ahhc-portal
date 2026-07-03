<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    protected $table = 'agreements';

    protected $fillable = [
        'title',
        'description',
        'content',
        'version',
        'is_required',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Participants this agreement is assigned to
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'agreement_participant')
            ->withTimestamps();
    }

    /**
     * Signatures for this agreement
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(AgreementSignature::class, 'agreement_id');
    }

    /**
     * Creator user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Last updater user
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if this is a required agreement
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * Check if this is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
