@extends('layouts.portal')

@section('title', 'My Profile')

@section('content')
    <div class="portal-page-header">
        <h1>My Profile</h1>
        <p>Review and update your worker profile details.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="profile-summary mb-4">
        <div class="d-flex align-items-center mb-4">
            <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile photo" class="rounded-circle me-3" width="96" height="96">
            <div>
                <h2 class="mb-1">{{ $worker->first_name }} {{ $worker->last_name }}</h2>
                <p class="text-muted mb-0">Worker profile</p>
            </div>
        </div>
        <div class="profile-row">
            <span>Name</span>
            <strong>{{ $worker->first_name }} {{ $worker->last_name }}</strong>
        </div>
        <div class="profile-row">
            <span>Email</span>
            <strong>{{ $worker->email }}</strong>
        </div>
        <div class="profile-row">
            <span>Phone</span>
            <strong>{{ $worker->phone ?? 'Not provided' }}</strong>
        </div>
        <div class="profile-row">
            <span>Active Assignments</span>
            <strong>{{ $activeAssignments }}</strong>
        </div>
        @if(isset($complianceDays))
            <div class="profile-row">
                <span>Compliance expires in</span>
                <strong>{{ $complianceDays }} day{{ $complianceDays === 1 ? '' : 's' }}</strong>
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('portal.worker.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label for="profile_photo" class="form-label">Profile photo</label>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="form-control">
                <div class="form-text">Upload a new profile photo (PNG, JPG, GIF, max 5MB).</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">First name</label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $worker->first_name) }}" required>
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">Last name</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $worker->last_name) }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $worker->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $worker->phone) }}">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" name="notes" rows="4" class="form-control">{{ old('notes', $worker->notes) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>

    <div class="mt-5">
        <h2>Security settings</h2>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Two-factor authentication</h5>
                @if(auth()->user()->mfa_enabled)
                    <p class="mb-1">MFA is currently <strong>enabled</strong> for your account.</p>
                    <p class="text-muted mb-3">Your worker account is protected with two-factor authentication.</p>
                    <form method="POST" action="{{ route('portal.mfa.disable') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Disable MFA</button>
                    </form>
                @else
                    <p class="mb-1">MFA is currently <strong>disabled</strong> for your account.</p>
                    <p class="text-muted mb-3">Enable two-factor authentication to strengthen your login security.</p>
                    <a href="{{ route('portal.mfa.setup') }}" class="btn btn-primary">Enable MFA</a>
                @endif
            </div>
        </div>
    </div>
@endsection
