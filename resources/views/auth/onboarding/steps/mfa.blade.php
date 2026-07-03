<section id="step-2" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">2</div>
            <div>
                <h2 class="h5 mb-1">MFA setup</h2>
                <p class="text-muted mb-0">Prepare to secure your account with multi-factor authentication.</p>
            </div>
        </div>
    </div>

    <div class="alert alert-info">If multi-factor authentication is required, you'll complete the setup after onboarding. If it's optional, you can enable it now or later.</div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="mfa_acknowledged" name="mfa_acknowledged" value="1" {{ old('mfa_acknowledged', $draftData['mfa_acknowledged'] ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="mfa_acknowledged">I understand that MFA may be required for my portal login.</label>
    </div>

    <div class="text-muted small">You can skip this step if you prefer, but your admin may require MFA before access is granted.</div>
</section>
