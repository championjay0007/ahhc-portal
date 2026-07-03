<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkerNomination;

class WorkerNominationPolicy
{
    /**
     * Determine whether the user can view any nominations.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the nomination.
     */
    public function view(User $user, WorkerNomination $nomination): bool
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }

        // Participant can only view their own
        if ($user->role === 'participant') {
            return $nomination->participant->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create nominations.
     */
    public function create(User $user): bool
    {
        return $user->role === 'participant';
    }

    /**
     * Determine whether the user can update the nomination.
     */
    public function update(User $user, WorkerNomination $nomination): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the nomination.
     */
    public function delete(User $user, WorkerNomination $nomination): bool
    {
        // Participant can only delete if submitted or rejected
        if ($user->role === 'participant') {
            return $nomination->participant->user_id === $user->id
                && in_array($nomination->status->value, ['Submitted', 'Rejected']);
        }

        // Admin can delete if not active
        if ($user->role === 'admin') {
            return ! in_array($nomination->status->value, ['Active', 'Assigned']);
        }

        return false;
    }

    /**
     * Determine whether the user can approve nominations.
     */
    public function approve(User $user, WorkerNomination $nomination): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can reject nominations.
     */
    public function reject(User $user, WorkerNomination $nomination): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can invite worker.
     */
    public function inviteWorker(User $user, WorkerNomination $nomination): bool
    {
        return $user->role === 'admin';
    }
}
