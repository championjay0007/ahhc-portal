@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-3">Registration Received</h3>
                    <p class="mb-3">Thanks — we've received your registration. Your account is pending approval by Allegiance Heart &amp; Home Care administrators.</p>
                    <p class="mb-3">We will notify you at <strong>{{ $email }}</strong> when your account is approved and you can access the portal.</p>
                    <a href="{{ route('portal.login') }}" class="btn btn-outline-primary">Return to login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
