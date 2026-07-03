<section id="step-8" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">8</div>
            <div>
                <h2 class="h5 mb-1">Review</h2>
                <p class="text-muted mb-0">Review your details before finalizing onboarding.</p>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h3 class="h6">Summary</h3>
        <dl class="row">
            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8">{{ $participant->email }}</dd>

            <dt class="col-sm-4">Preferred name</dt>
            <dd class="col-sm-8">{{ old('preferred_name', $draftData['preferred_name'] ?? $participant->preferred_name ?? $participant->first_name) }}</dd>

            <dt class="col-sm-4">Phone</dt>
            <dd class="col-sm-8">{{ old('phone', $draftData['phone'] ?? $participant->phone) }}</dd>

            <dt class="col-sm-4">Support person</dt>
            <dd class="col-sm-8">
                {{ trim(old('support_first_name', $draftData['support_first_name'] ?? $supportPerson?->first_name ?? '') . ' ' . old('support_last_name', $draftData['support_last_name'] ?? $supportPerson?->last_name ?? '')) ?: 'Not provided' }}
            </dd>
        </dl>
    </div>

    <div class="alert alert-secondary">If the details look correct, click Finalize to complete the onboarding process.</div>
</section>
