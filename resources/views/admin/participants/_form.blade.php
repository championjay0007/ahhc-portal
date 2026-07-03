<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">User name</label>
        <input type="text" name="name" value="{{ old('name', $participant->user->name ?? (old('first_name') . ' ' . old('last_name'))) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $participant->email ?? $participant->user->email ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $participant->phone ?? $participant->user->phone ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="active"{{ old('status', $participant->status ?? 'active') === 'active' ? ' selected' : '' }}>Active</option>
            <option value="inactive"{{ old('status', $participant->status ?? '') === 'inactive' ? ' selected' : '' }}>Inactive</option>
            <option value="onboarding"{{ old('status', $participant->status ?? '') === 'onboarding' ? ' selected' : '' }}>Onboarding</option>
            <option value="closed"{{ old('status', $participant->status ?? '') === 'closed' ? ' selected' : '' }}>Closed</option>
        </select>
        <small class="text-muted">Select onboarding to create an invitation link instead of setting a password.</small>
    </div>

    <div class="col-md-6">
        <label class="form-label">Participant number</label>
        <input type="text" name="participant_number" value="{{ old('participant_number', $participant->participant_number ?? '') }}" class="form-control" {{ isset($participant) ? '' : '' }}>
    </div>
    <div class="col-md-6">
        <label class="form-label">Preferred name</label>
        <input type="text" name="preferred_name" value="{{ old('preferred_name', $participant->preferred_name ?? '') }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">First name</label>
        <input type="text" name="first_name" value="{{ old('first_name', $participant->first_name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Last name</label>
        <input type="text" name="last_name" value="{{ old('last_name', $participant->last_name ?? '') }}" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Date of birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($participant->date_of_birth)->format('Y-m-d') ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Primary language</label>
        <input type="text" name="primary_language" value="{{ old('primary_language', $participant->primary_language ?? '') }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ old('address', $participant->address ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">City</label>
        <input type="text" name="city" value="{{ old('city', $participant->city ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">State</label>
        <input type="text" name="state" value="{{ old('state', $participant->state ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Postcode</label>
        <input type="text" name="postcode" value="{{ old('postcode', $participant->postcode ?? '') }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Care plan start</label>
        <input type="date" name="care_plan_start_date" value="{{ old('care_plan_start_date', optional($participant->care_plan_start_date)->format('Y-m-d') ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Care plan end</label>
        <input type="date" name="care_plan_end_date" value="{{ old('care_plan_end_date', optional($participant->care_plan_end_date)->format('Y-m-d') ?? '') }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Assigned support person</label>
        <select name="assigned_support_person_id" class="form-select">
            <option value="">None</option>
            @foreach($supportPeople as $supportPerson)
                <option value="{{ $supportPerson->id }}"{{ old('assigned_support_person_id', $participant->assigned_support_person_id ?? '') == $supportPerson->id ? ' selected' : '' }}>
                    {{ $supportPerson->first_name }} {{ $supportPerson->last_name }} {{ $supportPerson->relationship ? '('.$supportPerson->relationship.')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-check form-switch mt-4">
            <input type="hidden" name="consent_to_share" value="0">
            <input type="checkbox" name="consent_to_share" value="1" class="form-check-input"{{ old('consent_to_share', $participant->consent_to_share ?? false) ? ' checked' : '' }}>
            <span class="form-check-label">Consent to share</span>
        </label>
    </div>

    <div class="col-md-6">
        <label class="form-label">Budget limit ($)</label>
        <input type="number" name="budget_limit_dollars" min="0" step="0.01" value="{{ old('budget_limit_dollars', isset($participant) ? number_format($participant->budget_limit_cents / 100, 2, '.', '') : '0.00') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Current budget used ($)</label>
        <input type="number" name="current_budget_used_dollars" min="0" step="0.01" value="{{ old('current_budget_used_dollars', isset($participant) ? number_format($participant->current_budget_used_cents / 100, 2, '.', '') : '0.00') }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Medical alerts</label>
        <textarea name="medical_alerts" rows="3" class="form-control">{{ old('medical_alerts', $participant->medical_alerts ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $participant->notes ?? '') }}</textarea>
    </div>

    @if(empty($participant->id))
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" value="{{ old('password') }}" {{ old('status', $participant->status ?? '') === 'onboarding' ? '' : 'required' }}>
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control" {{ old('status', $participant->status ?? '') === 'onboarding' ? '' : 'required' }}>
        </div>
        <div class="col-12">
            <small class="text-muted">If you choose onboarding status, you can leave the password blank and the participant will receive a secure invitation email.</small>
        </div>
    @endif

    <div class="col-12 mt-2">
        <small class="text-muted">Budget amounts are entered in dollars and converted to cents for storage.</small>
    </div>
</div>
