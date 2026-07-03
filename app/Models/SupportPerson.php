<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportPerson extends Model
{
    protected $fillable = [
        'user_id',
        'relationship',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'postcode',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'assigned_support_person_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function preApprovalRequests(): HasMany
    {
        return $this->hasMany(PreApprovalRequest::class);
    }
}
