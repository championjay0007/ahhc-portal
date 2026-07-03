<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkerComplianceDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkerComplianceDocumentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function verify(User $user, WorkerComplianceDocument $document): bool
    {
        return $user->hasRole('admin');
    }

    public function reject(User $user, WorkerComplianceDocument $document): bool
    {
        return $user->hasRole('admin');
    }
}
