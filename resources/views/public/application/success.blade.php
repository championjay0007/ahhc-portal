@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0 text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle" style="font-size: 4rem; color: #198754;"></i>
                    </div>

                    <h2 class="card-title mb-3">Application Submitted</h2>

                    <p class="text-muted mb-4">
                        Thank you for submitting your application to Allegiance Heart &amp; Home Care Self-Management Support.
                    </p>

                    <div class="alert alert-info mb-4">
                        <p class="mb-0">
                            Our team will review your application and contact you within 5-7 business days. 
                            Please check your email for updates.
                        </p>
                    </div>

                    <h5 class="mt-4 mb-3">What happens next?</h5>
                    <ul class="text-start" style="display: inline-block;">
                        <li>Application reviewed by the Allegiance Heart &amp; Home Care team</li>
                        <li>You'll receive an email notification</li>
                        <li>If approved, you'll get an onboarding invitation</li>
                        <li>Complete onboarding to activate your account</li>
                    </ul>

                    <hr class="my-4">

                    <p class="text-muted small">
                        If you have any questions, please contact our support team at 
                        <a href="mailto:intake@allegiancehearthomecare.com.au">intake@allegiancehearthomecare.com.au</a>
                    </p>

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('public.home') }}" class="btn btn-primary">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
