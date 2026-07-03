@extends('layouts.auth')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-3">Reset password</h3>
                    <p class="text-muted mb-4">Choose a new password for your account.</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ $email ?? old('email') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <input id="password" type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Reset password</button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="{{ route('portal.login') }}" class="text-decoration-none">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
