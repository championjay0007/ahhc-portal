<?php

namespace App\Policies;

use App\Models\ParticipantAssignment;
use App\Models\User;
use App\Services\ComplianceService;

class ParticipantAssignmentPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, ParticipantAssignment $assignment): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, ParticipantAssignment $assignment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Check if assignment is allowed based on worker compliance
     */
    public function assignWorker(User $user, ParticipantAssignment $assignment): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        $complianceService = app(ComplianceService::class);

        return $complianceService->canWorkerBeAssigned($assignment->worker);
    }
}
