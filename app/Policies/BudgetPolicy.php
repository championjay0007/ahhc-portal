<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user)
    {
        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->hasRole ?? true);
    }

    public function view(User $user, Budget $budget)
    {
        $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->hasRole ?? false);
        return $user->id === $budget->participant_id || $isAdmin;
    }

    public function update(User $user, Budget $budget)
    {
        $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->hasRole ?? false);
        return $user->id === $budget->participant_id || $isAdmin;
    }

    public function delete(User $user, Budget $budget)
    {
        $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->hasRole ?? false);
        return $user->id === $budget->participant_id || $isAdmin;
    }
}
