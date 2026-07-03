<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Participant;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerNomination;
use App\Policies\BudgetPolicy;
use App\Policies\ParticipantPolicy;
use App\Policies\WorkerComplianceDocumentPolicy;
use App\Policies\WorkerNominationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->registerPolicies();
    }

    protected function registerPolicies()
    {
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Participant::class, ParticipantPolicy::class);
        Gate::policy(WorkerComplianceDocument::class, WorkerComplianceDocumentPolicy::class);
        Gate::policy(WorkerNomination::class, WorkerNominationPolicy::class);
    }
}
