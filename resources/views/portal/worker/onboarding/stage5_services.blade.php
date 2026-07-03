@extends('layouts.auth')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2>Worker Onboarding - Stage 5: Approved Services</h2>
                <span class="badge bg-info mt-2 mt-md-0">Stage 5 of 6</span>
            </div>

            <div class="alert alert-info">
                <h6 class="alert-heading">Service Categories Approved for You</h6>
                <p class="mb-0">Allegiance Heart &amp; Home Care has defined the service categories you are approved to provide. Review them below to understand the scope of your work.</p>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Your Approved Services</h6>
                        </div>
                        <div class="card-body">
                            @if ($approvedServices->isEmpty())
                                <div class="alert alert-warning">
                                    <p class="mb-0">No services have been approved yet. Allegiance Heart &amp; Home Care is still reviewing your eligibility.</p>
                                </div>
                            @else
                                <div class="row g-3">
                                    @foreach ($approvedServices as $service)
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title">{{ $service->service_category }}</h6>
                                                    @if ($service->description)
                                                        <p class="card-text small text-muted">{{ $service->description }}</p>
                                                    @endif
                                                    <small class="d-block mt-2">
                                                        <strong>Approved:</strong> {{ $service->approved_at->format('M d, Y') }}
                                                    </small>
                                                    @if ($service->approval_end_date)
                                                        <small class="d-block text-warning">
                                                            <strong>Valid until:</strong> {{ $service->approval_end_date->format('M d, Y') }}
                                                        </small>
                                                    @else
                                                        <small class="d-block text-success">
                                                            <strong>Valid:</strong> Indefinitely
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h6 class="alert-heading">What's Next?</h6>
                        <p class="mb-0">Once you review your approved services, Allegiance Heart &amp; Home Care will proceed to Stage 6 where you'll be assigned to participants who need your services.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Service Guidelines</h6>
                        </div>
                        <div class="card-body small">
                            <p><strong>Important:</strong></p>
                            <ul>
                                <li>Only provide services in your approved categories</li>
                                <li>Always complete care notes after each service</li>
                                <li>Report any issues immediately</li>
                                <li>Follow all Allegiance Heart &amp; Home Care policies and guidelines</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
