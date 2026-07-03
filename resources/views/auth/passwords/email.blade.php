@extends('layouts.auth')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-3">Reset your password</h3>
                    <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>

                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
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
