<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole ?? true;
    }

    public function view(User $user, Budget $budget)
    {
        return $user->id === $budget->participant_id || ($user->hasRole ?? false);
    }

    public function update(User $user, Budget $budget)
    {
        return $user->id === $budget->participant_id || ($user->hasRole ?? false);
    }

    public function delete(User $user, Budget $budget)
    {
        return $user->id === $budget->participant_id || ($user->hasRole ?? false);
    }
}
