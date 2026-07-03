@extends('layouts.admin')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h1 class="h4">Enquiry #{{ $enquiry->id }}</h1>
            <p class="text-muted mb-0">Details and management options for this public website enquiry.</p>
        </div>
        <a href="{{ route('portal.admin.enquiries.index') }}" class="btn btn-outline-light">Back to Enquiries</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Enquiry Details</h5>
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <strong>Name</strong>
                            <p class="mb-0">{{ $enquiry->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Email</strong>
                            <p class="mb-0">{{ $enquiry->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Phone</strong>
                            <p class="mb-0">{{ $enquiry->phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Role</strong>
                            <p class="mb-0">{{ ucwords(str_replace('_', ' ', $enquiry->role)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status</strong>
                            <p class="mb-0">{{ $enquiry->status }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Assigned To</strong>
                            <p class="mb-0">{{ $enquiry->assignedTo?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Support at Home Status</strong>
                            <p class="mb-0">{{ $enquiry->support_at_home_status ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Message</strong>
                            <p class="mb-0">{{ $enquiry->message }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Manage Enquiry</h5>
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    <form method="POST" action="{{ route('portal.admin.enquiries.update', $enquiry) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ $enquiry->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign Staff</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">Unassigned</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $enquiry->assigned_to === $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="5" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $enquiry->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </form>

                    @if($enquiry->role === 'participant')
                        <form method="POST" action="{{ route('portal.admin.enquiries.invite_participant', $enquiry) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">Send participant onboarding invitation</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('portal.admin.enquiries.destroy', $enquiry) }}" class="mt-3" onsubmit="return confirm('Delete this enquiry permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">Delete enquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
