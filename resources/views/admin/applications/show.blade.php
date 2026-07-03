@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Review Application</h1>
            <p class="text-muted">Review details and choose the next action for this applicant.</p>
        </div>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary">Back to applications</a>
    </div>

    <div class="row gy-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Applicant Details</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Name</strong>
                            <p class="mb-0">{{ $application->first_name }} {{ $application->last_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email</strong>
                            <p class="mb-0">{{ $application->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Phone</strong>
                            <p class="mb-0">{{ $application->phone }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status</strong>
                            <p class="mb-0">
                                <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : ($application->status === 'under_review' ? 'warning' : 'secondary')) }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <h3 class="h6 mt-4 mb-3">Address</h3>
                    <p class="mb-1">{{ $application->address }}</p>
                    <p class="mb-0">{{ $application->city }}, {{ $application->state }} {{ $application->postcode }}</p>

                    <h3 class="h6 mt-4 mb-3">Support Details</h3>
                    <p class="mb-1"><strong>Disability category:</strong> {{ ucwords(str_replace('_', ' ', $application->disability_category ?? 'Not provided')) }}</p>
                    <p class="mb-1"><strong>Funding source:</strong> {{ ucwords(str_replace('_', ' ', $application->funding_source ?? 'Not provided')) }}</p>
                    <p class="mb-0"><strong>Support needs:</strong> {{ $application->support_needs ?? 'Not provided' }}</p>
                </div>
            </div>

            @if($application->rejected_reason)
                <div class="card mb-4 border-danger">
                    <div class="card-body">
                        <h3 class="h6 text-danger mb-3">Rejection Reason</h3>
                        <p class="mb-0">{{ $application->rejected_reason }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h6 mb-3">Actions</h3>

                    <form action="{{ route('admin.applications.approve', $application) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">Approve and Invite</button>
                    </form>

                    <form action="{{ route('admin.applications.under_review', $application) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100">Mark Under Review</button>
                    </form>

                    <div class="card border-secondary">
                        <div class="card-body">
                            <h4 class="h6 mb-3">Reject Application</h4>
                            <form action="{{ route('admin.applications.reject', $application) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Reason for rejection" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">Reject Application</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-info">
                <div class="card-body">
                    <p class="text-muted small mb-0">Approve this application to send an onboarding invitation. Participants cannot access the portal until their onboarding is completed and approved.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
