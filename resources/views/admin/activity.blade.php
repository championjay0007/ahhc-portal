@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">
                            <i class="fas fa-chart-line text-primary me-2"></i>Activity Log
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Monitor system events and administrator actions in real-time
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-soft-primary text-primary px-3 py-2">
                            <i class="fas fa-clock me-1"></i>
                            {{ $activities->total() }} Total Events
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-soft-primary p-2 me-3">
                        <i class="fas fa-filter text-primary"></i>
                    </div>
                    <h5 class="mb-0 fw-semibold">Filter Activities</h5>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-medium small text-muted">
                                <i class="fas fa-search me-1"></i>Search
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" 
                                       name="search" 
                                       class="form-control border-start-0 ps-0" 
                                       placeholder="Search activities..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-medium small text-muted">
                                <i class="fas fa-user me-1"></i>User
                            </label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @if(request('user_id') == $user->id) selected @endif>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-medium small text-muted">
                                <i class="fas fa-tag me-1"></i>Action Type
                            </label>
                            <select name="action" class="form-select">
                                <option value="">All Actions</option>
                                @foreach($actions as $act)
                                    <option value="{{ $act }}" @if(request('action') == $act) selected @endif>
                                        {{ ucfirst($act) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-medium small text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>Date Range
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar text-muted"></i>
                                </span>
                                <input type="date" 
                                       name="date_from" 
                                       class="form-control" 
                                       value="{{ request('date_from') }}"
                                       placeholder="From">
                                <span class="input-group-text bg-light px-2">to</span>
                                <input type="date" 
                                       name="date_to" 
                                       class="form-control" 
                                       value="{{ request('date_to') }}"
                                       placeholder="To">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 align-items-center">
                        <button class="btn btn-primary px-4" type="submit">
                            <i class="fas fa-check-circle me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('portal.admin.activity', array_merge(request()->except('page'), ['export' => 'csv'])) }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </a>
                        @if(request()->anyFilled(['search', 'user_id', 'action', 'date_from', 'date_to']))
                            <a href="{{ route('portal.admin.activity') }}" class="btn btn-link text-muted">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if($activities->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-inbox fa-4x text-muted opacity-25"></i>
                        </div>
                        <h5 class="text-muted fw-normal">No Activities Found</h5>
                        <p class="text-muted small">Try adjusting your search or filter criteria</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase">
                                        <i class="fas fa-user me-1"></i>User
                                    </th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">
                                        <i class="fas fa-bolt me-1"></i>Action
                                    </th>
                                    <th class="py-3 text-muted fw-semibold small text-uppercase">
                                        <i class="fas fa-globe me-1"></i>IP Address
                                    </th>
                                    <th class="pe-4 py-3 text-muted fw-semibold small text-uppercase">
                                        <i class="fas fa-clock me-1"></i>Timestamp
                                    </th>
                                    <th class="pe-4 py-3 text-muted fw-semibold small text-uppercase">
                                        <i class="fas fa-trash me-1"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                    <tr class="activity-row">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px; font-size: 14px;">
                                                    {{ strtoupper(substr(optional($activity->user)->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-medium text-dark">
                                                        {{ optional($activity->user)->name ?? 'System' }}
                                                    </span>
                                                    @if(!$activity->user)
                                                        <span class="badge bg-soft-warning text-warning ms-1" 
                                                              style="font-size: 0.65rem;">AUTO</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-info text-info px-2 py-1">
                                                {{ $activity->action }}
                                            </span>
                                        </td>
                                        <td>
                                            <code class="text-muted small">
                                                {{ $activity->ip_address ?? '—' }}
                                            </code>
                                        </td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-calendar-alt text-muted me-2 small"></i>
                                                <span class="text-muted small">
                                                    {{ optional($activity->created_at)->format('M d, Y') }}
                                                </span>
                                                <span class="mx-2 text-muted">•</span>
                                                <i class="far fa-clock text-muted me-2 small"></i>
                                                <span class="text-muted small">
                                                    {{ optional($activity->created_at)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="pe-4">
                                            <form method="POST" action="{{ route('portal.admin.activity.destroy', $activity) }}" class="d-inline" onsubmit="return confirm('Delete this activity log entry?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} 
                                of {{ $activities->total() }} entries
                            </small>
                            <div>
                                {{ $activities->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
        }
        .bg-soft-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
        .activity-row {
            transition: all 0.2s ease;
        }
        .activity-row:hover {
            background-color: #f8f9fa;
            transform: translateX(4px);
        }
        .card {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        .table th {
            letter-spacing: 0.5px;
        }
    </style>
@endsection