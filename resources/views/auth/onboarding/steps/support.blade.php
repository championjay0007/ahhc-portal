<section id="step-5" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">5</div>
            <div>
                <h2 class="h5 mb-1">Support person</h2>
                <p class="text-muted mb-0">Provide a support person who helps manage your care instructions.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Support person first name</label>
            <input type="text" name="support_first_name" class="form-control" value="{{ old('support_first_name', $draftData['support_first_name'] ?? $supportPerson?->first_name ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Support person last name</label>
            <input type="text" name="support_last_name" class="form-control" value="{{ old('support_last_name', $draftData['support_last_name'] ?? $supportPerson?->last_name ?? '') }}">
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Relationship</label>
            <input type="text" name="support_relationship" class="form-control" value="{{ old('support_relationship', $draftData['support_relationship'] ?? $supportPerson?->relationship ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="support_phone" class="form-control" value="{{ old('support_phone', $draftData['support_phone'] ?? $supportPerson?->phone ?? '') }}">
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $draftData['support_email'] ?? $supportPerson?->email ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Address</label>
            <input type="text" name="support_address" class="form-control" value="{{ old('support_address', $draftData['support_address'] ?? $supportPerson?->address ?? '') }}">
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">City</label>
            <input type="text" name="support_city" class="form-control" value="{{ old('support_city', $draftData['support_city'] ?? $supportPerson?->city ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">State</label>
            <input type="text" name="support_state" class="form-control" value="{{ old('support_state', $draftData['support_state'] ?? $supportPerson?->state ?? '') }}">
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Postcode</label>
        <input type="text" name="support_postcode" class="form-control" value="{{ old('support_postcode', $draftData['support_postcode'] ?? $supportPerson?->postcode ?? '') }}">
    </div>

    <div class="form-text mt-2">A support person can be a family member, guardian, or caregiver who you trust.</div>
</section>
