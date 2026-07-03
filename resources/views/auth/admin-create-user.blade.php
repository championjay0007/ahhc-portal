@extends('layouts.admin')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4">Create Admin Portal User</h3>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="">Select role</option>
                                <option value="participant" {{ old('role') === 'participant' ? 'selected' : '' }}>Participant</option>
                                <option value="worker" {{ old('role') === 'worker' ? 'selected' : '' }}>Worker</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="force_dashboard" name="force_dashboard" value="1" {{ old('force_dashboard') ? 'checked' : '' }}>
                            <label class="form-check-label" for="force_dashboard">
                                Force direct dashboard login (skip onboarding flow)
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
