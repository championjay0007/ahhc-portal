@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">Reports & Analytics</h1>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Care Reviews Report -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Care Reviews</h5>
                </div>
                <div class="card-body">
                    <p>View care review statistics, overdue items, and completion rates.</p>
                    <a href="{{ route('portal.admin.care_reviews.dashboard') }}" class="btn btn-info btn-sm">
                        <i class="bi bi-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Compliance Report -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Compliance</h5>
                </div>
                <div class="card-body">
                    <p>Monitor compliance documents, expiring items, and worker compliance status.</p>
                    <a href="{{ route('portal.admin.compliance.dashboard') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Activity Log -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Activity Log</h5>
                </div>
                <div class="card-body">
                    <p>Review system activity and user actions across the platform.</p>
                    <a href="{{ route('portal.admin.activity') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right"></i> View Activity
                    </a>
                </div>
            </div>
        </div>

        <!-- Pre-Approvals Report -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Pre-Approvals</h5>
                </div>
                <div class="card-body">
                    <p>Track pre-approval requests, approvals, and rejections.</p>
                    <a href="{{ route('portal.admin.pre_approvals') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-arrow-right"></i> View Pre-Approvals
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
