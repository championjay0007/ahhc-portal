@php
    $worker = $worker ?? null;
    $isEdit = $worker && $worker->exists;
    $name = old('name', $worker?->user?->name ?? trim(($worker?->first_name ?? '') . ' ' . ($worker?->last_name ?? '')));
    $email = old('email', $worker?->email);
    $phone = old('phone', $worker?->phone);
    $status = old('status', $worker?->status ?? 'active');
    $qualification = old('qualification', $worker?->qualification);
    $availability = old('availability', $worker?->availability);
    $complianceExpiry = old('compliance_expiry_at', optional($worker?->compliance_expiry_at)->format('Y-m-d'));
    $backgroundExpiry = old('background_check_expiry_at', optional($worker?->background_check_expiry_at)->format('Y-m-d'));
    $vehicleType = old('vehicle_type', $worker?->vehicle_type);
    $notes = old('notes', $worker?->notes);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input name="name" value="{{ $name }}" class="form-control" required>
        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input name="email" type="email" value="{{ $email }}" class="form-control" required>
        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input name="phone" value="{{ $phone }}" class="form-control">
        @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="active"{{ $status === 'active' ? ' selected' : '' }}>Active</option>
            <option value="inactive"{{ $status === 'inactive' ? ' selected' : '' }}>Inactive</option>
        </select>
        @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Qualification</label>
        <input name="qualification" value="{{ $qualification }}" class="form-control">
        @error('qualification') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Availability</label>
        <input name="availability" value="{{ $availability }}" class="form-control">
        @error('availability') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Compliance expiry</label>
        <input name="compliance_expiry_at" type="date" value="{{ $complianceExpiry }}" class="form-control">
        @error('compliance_expiry_at') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Background check expiry</label>
        <input name="background_check_expiry_at" type="date" value="{{ $backgroundExpiry }}" class="form-control">
        @error('background_check_expiry_at') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Vehicle type</label>
        <input name="vehicle_type" value="{{ $vehicleType }}" class="form-control">
        @error('vehicle_type') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="1" class="form-control">{{ $notes }}</textarea>
        @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    @if($showPasswordFields ?? false)
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
            @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm password</label>
            <input name="password_confirmation" type="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
        </div>
        @if($isEdit)
            <div class="col-12">
                <div class="form-text">Leave password blank to keep the current password.</div>
            </div>
        @endif
    @endif

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Save worker' }}</button>
    </div>
</div>
