<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    use HasFactory;

    protected $table = 'onboarding_progress';

    protected $fillable = [
        'participant_id',
        'current_step',
        'completed_steps',
        'draft_data',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'draft_data' => 'array',
        'completed_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function markStepComplete(int $step): void
    {
        $steps = $this->completed_steps ?? [];
        if (! in_array($step, $steps, true)) {
            $steps[] = $step;
            sort($steps);
        }
        $this->completed_steps = $steps;
    }

    public function stepProgress(): int
    {
        return (int) round((($this->current_step - 1) / 8) * 100);
    }

    public function completionPercentage(): int
    {
        return (int) round((count($this->completed_steps ?? []) / 8) * 100);
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
