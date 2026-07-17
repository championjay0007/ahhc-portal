<section id="step-3" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">3</div>
            <div>
                <h2 class="h5 mb-1">Profile information</h2>
                <p class="text-muted mb-0">Tell us about yourself so your care team has the right details.</p>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Preferred name</label>
            <input type="text" name="preferred_name" class="form-control" value="{{ old('preferred_name', $draftData['preferred_name'] ?? $participant->preferred_name ?? $participant->first_name) }}" data-required="true">
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone number</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $draftData['phone'] ?? $participant->phone) }}" data-required="true">
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Date of birth (optional)</label>
            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $draftData['date_of_birth'] ?? optional($participant->date_of_birth)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Primary language (optional)</label>
            <input type="text" name="primary_language" class="form-control" value="{{ old('primary_language', $draftData['primary_language'] ?? $participant->primary_language) }}">
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Address (optional)</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $draftData['address'] ?? $participant->address) }}">
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $draftData['city'] ?? $participant->city) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="{{ old('state', $draftData['state'] ?? $participant->state) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Postcode</label>
            <input type="text" name="postcode" class="form-control" value="{{ old('postcode', $draftData['postcode'] ?? $participant->postcode) }}">
        </div>
    </div>
</section>
