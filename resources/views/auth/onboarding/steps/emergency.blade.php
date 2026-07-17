<section id="step-4" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">4</div>
            <div>
                <h2 class="h5 mb-1">Emergency contacts</h2>
                <p class="text-muted mb-0">Add at least one person we can contact in an emergency.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Contact name</label>
            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $draftData['emergency_contact_name'] ?? '') }}" data-required="true">
        </div>
        <div class="col-md-6">
            <label class="form-label">Relationship</label>
            <input type="text" name="emergency_contact_relationship" class="form-control" value="{{ old('emergency_contact_relationship', $draftData['emergency_contact_relationship'] ?? '') }}" data-required="true">
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Contact phone</label>
            <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $draftData['emergency_contact_phone'] ?? '') }}" data-required="true">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email address (optional)</label>
            <input type="email" name="emergency_contact_email" class="form-control" value="{{ old('emergency_contact_email', $draftData['emergency_contact_email'] ?? '') }}">
        </div>
    </div>

    <div class="form-text mt-2">We will only use this information if we cannot reach you directly.</div>
</section>
