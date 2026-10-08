@extends('layouts.public')

@section('content')
    <section class="section-padding" style="min-height: 75vh; padding-top: 9rem; background: white;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="text-center p-4 p-md-5 rounded-4 bg-white shadow-sm">
                        <div class="mb-4" style="color: var(--brand);">
                            <i class="bi bi-envelope-check-fill" style="font-size: 3.5rem;"></i>
                        </div>
                        <h1 class="mb-3" style="font-weight: 800; color: var(--text);">
                            Thank You — We’ve Received Your Enquiry
                        </h1>
                        <p class="mb-3" style="color: var(--text-secondary); line-height: 1.7;">
                            A member of our intake team will contact you to discuss your Support at Home needs, your preferred self-management arrangement and the next steps.
                        </p>
                        <p class="mb-4" style="color: var(--text-secondary);">
                            If you would prefer to speak with us sooner, call <a href="tel:+61287309049">02 8730 9049</a>.
                        </p>
                        <a href="{{ route('public.home') }}" class="btn-primary-custom">
                            <i class="bi bi-house-door-fill"></i> Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
