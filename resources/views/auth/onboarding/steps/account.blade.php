<section id="step-1" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">1</div>
            <div>
                <h2 class="h5 mb-1">Account setup</h2>
                <p class="text-muted mb-0">Create your portal access credentials and confirm your email.</p>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" class="form-control" value="{{ $participant->email }}" disabled>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" data-required="true" required>
        <div class="invalid-feedback">Enter a secure password at least 8 characters long.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm password</label>
        <input type="password" name="password_confirmation" class="form-control" data-required="true" required>
        <div class="invalid-feedback">Confirm your password.</div>
    </div>
</section>
