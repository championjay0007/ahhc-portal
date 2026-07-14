<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'system_admin';
    }

    public function view(User $user, Shift $shift): bool
    {
        if ($user->role === 'system_admin') {
            return true;
        }

        if ($user->role === 'admin') {
            return true;
        }

        // Workers can view their assigned shifts
        if ($user->worker && $shift->worker_id === $user->worker->id) {
            return true;
        }

        // Participants can view shifts assigned to them
        if ($user->participant && $shift->participant_id === $user->participant->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'system_admin';
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->role === 'admin' || $user->role === 'system_admin';
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->role === 'system_admin' || 
               ($user->role === 'admin' && in_array($shift->status, [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED]));
    }

    public function cancel(User $user, Shift $shift): bool
    {
        return $user->role === 'admin' || $user->role === 'system_admin';
    }

    // Worker actions
    public function confirm(User $user, Shift $shift): bool
    {
        return $user->worker && $shift->worker_id === $user->worker->id && $shift->status === Shift::STATUS_SCHEDULED;
    }

    public function start(User $user, Shift $shift): bool
    {
        return $user->worker && $shift->worker_id === $user->worker->id && 
               in_array($shift->status, [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED]);
    }

    public function complete(User $user, Shift $shift): bool
    {
        return $user->worker && $shift->worker_id === $user->worker->id && $shift->status === Shift::STATUS_IN_PROGRESS;
    }
}
