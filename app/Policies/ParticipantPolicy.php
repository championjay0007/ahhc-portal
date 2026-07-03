<?php

namespace App\Policies;

use App\Models\Participant;
use App\Models\User;
use App\Models\Worker;

class ParticipantPolicy
{
    /**
     * Determine if worker can view participant
     */
    public function view(User $user, Participant $participant): bool
    {
        // Workers can only view assigned participants and only in Stage 6+
        if ($user->role === 'worker') {
            $worker = $user->worker;

            // Worker must be in Stage 6 (fully assigned) to view participant data
            if (! $worker || ! $worker->canAccessParticipantData()) {
                return false;
            }

            // Check if worker has active assignment to this participant
            return $worker->assignments()
                ->where('participant_id', $participant->id)
                ->where('status', 'active')
                ->exists();
        }

        // Admins and system admins can view any participant
        return in_array($user->role, ['admin', 'system_admin']);
    }

    /**
     * Determine if worker can view limited participant data only
     */
    public function viewLimited(User $user, Participant $participant): bool
    {
        if ($user->role === 'worker') {
            return $this->view($user, $participant);
        }

        return true;
    }

    /**
     * Workers cannot edit participant records
     */
    public function update(User $user, Participant $participant): bool
    {
        return in_array($user->role, ['admin', 'system_admin']);
    }

    /**
     * Workers cannot delete participants
     */
    public function delete(User $user, Participant $participant): bool
    {
        return in_array($user->role, ['admin', 'system_admin']);
    }
}
