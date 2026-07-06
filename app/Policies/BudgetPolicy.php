<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Budget $budget)
    {
        $isAdmin = $user->role === 'admin';
        return optional($user->participant)->id === $budget->participant_id || $isAdmin;
    }

    public function update(User $user, Budget $budget)
    {
        $isAdmin = $user->role === 'admin';
        return optional($user->participant)->id === $budget->participant_id || $isAdmin;
    }

    public function delete(User $user, Budget $budget)
    {
        $isAdmin = $user->role === 'admin';
        return optional($user->participant)->id === $budget->participant_id || $isAdmin;
    }
}
