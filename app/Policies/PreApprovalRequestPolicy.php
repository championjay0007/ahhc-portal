<?php

namespace App\Policies;

use App\Models\PreApprovalRequest;
use App\Models\User;

class PreApprovalRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'participant';
    }

    public function view(User $user, PreApprovalRequest $preApprovalRequest): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'participant') {
            return optional($user->participant)->id === $preApprovalRequest->participant_id;
        }

        return false;
    }

    public function approve(User $user, PreApprovalRequest $preApprovalRequest): bool
    {
        return $user->role === 'admin';
    }

    public function reject(User $user, PreApprovalRequest $preApprovalRequest): bool
    {
        return $user->role === 'admin';
    }
}
