@extends('layouts.admin')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Edit User</h3>
                            <p class="text-muted mb-0">Update user details, status, and password for this account.</p>
                        </div>
                        <a href="{{ route('portal.admin.users.show', $user) }}" class="btn btn-outline-secondary">Back to profile</a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input id="role" type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="force_dashboard" name="force_dashboard" value="1" {{ old('force_dashboard', $user->force_dashboard) ? 'checked' : '' }}>
                            <label class="form-check-label" for="force_dashboard">
                                Force direct dashboard login (skip onboarding flow)
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New password</label>
                                    <input id="password" type="password" name="password" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
