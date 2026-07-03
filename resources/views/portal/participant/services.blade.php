@extends('layouts.portal')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 mb-2">My Services / Shifts</h1>
            <p class="text-muted mb-0">View approved services, booked shifts, service dates and assigned workers.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">{{ $activeAssignments->count() }} approved shift{{ $activeAssignments->count() === 1 ? '' : 's' }}</span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card portal-card p-4 h-100 border-start border-primary">
                <h5 class="mb-3">Care Plan Summary</h5>
                <p class="text-muted mb-1">Approved care plan period</p>
                <h6 class="mb-3">
                    {{ optional($participant->care_plan_start_date)->format('d M Y') ?? 'Not set' }}
                    &mdash;
                    {{ optional($participant->care_plan_end_date)->format('d M Y') ?? 'Not set' }}
                </h6>
                <p class="text-muted mb-1">Assigned support person</p>
                <p class="mb-3">
                    @if($participant->supportPerson)
                        <strong>{{ $participant->supportPerson->first_name }} {{ $participant->supportPerson->last_name }}</strong><br>
                        <a href="mailto:{{ $participant->supportPerson->email }}">{{ $participant->supportPerson->email }}</a>
                    @else
                        <span class="text-muted">No support person assigned yet.</span>
                    @endif
                </p>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="card bg-light p-3 h-100">
                            <p class="text-muted mb-1">Active services</p>
                            <h5 class="mb-0">{{ $activeAssignments->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light p-3 h-100">
                            <p class="text-muted mb-1">Recent assignments</p>
                            <h5 class="mb-0">{{ $recentAssignments->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card portal-card p-4 h-100 border-start border-info">
                <h5 class="mb-3">Upcoming Shifts</h5>
                @if($upcomingShifts->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-3 mb-0">No upcoming shifts scheduled. Contact your support person for updates.</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($upcomingShifts as $shift)
                            <li class="list-group-item px-0 py-3 border-top-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <strong>{{ $shift->service_type ?? 'Service' }}</strong>
                                        <p class="small text-muted mb-0">{{ $shift->shift_date?->format('d M Y') }} · {{ $shift->start_time }} &ndash; {{ $shift->end_time }}</p>
                                    </div>
                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $shift->status)) }}</span>
                                </div>
                                <div class="small text-muted mt-2">
                                    Worker: {{ $shift->worker?->first_name ? $shift->worker->first_name . ' ' . $shift->worker->last_name : 'Not assigned' }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <h5 class="mb-3">Approved Services</h5>
                @if($approvedServices->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-3 mb-0">No approved services found. Contact your support person for updates.</p>
                    </div>
                @else
                    @foreach($approvedServices as $serviceType => $assignments)
                        <div class="mb-3">
                            <h6 class="mb-2">{{ $serviceType }}</h6>
                            <p class="small text-muted mb-2">{{ $assignments->count() }} active assignment{{ $assignments->count() === 1 ? '' : 's' }}</p>
                            <ul class="list-group list-group-flush">
                                @foreach($assignments as $assignment)
                                    <li class="list-group-item px-0 py-2 border-top-0 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <div>
                                                <strong>{{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}</strong>
                                                <p class="small text-muted mb-0">{{ $assignment->worker->role_type ?? 'Worker' }}</p>
                                            </div>
                                            <span class="badge bg-success">{{ $assignment->is_primary ? 'Primary' : 'Secondary' }}</span>
                                        </div>
                                        <div class="small text-muted mt-2">
                                            <span class="d-block">Shift: {{ $assignment->start_date?->format('d M Y') ?? 'TBC' }}
                                            @if($assignment->end_date)
                                                &ndash; {{ $assignment->end_date->format('d M Y') }}
                                            @else
                                                (Ongoing)
                                            @endif</span>
                                            <span class="d-block">Status: {{ ucfirst($assignment->status) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card portal-card p-4 border-start border-secondary">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h5 class="mb-1">Worker Assignment Record</h5>
                        <small class="text-muted">All recent shift bookings and assignment details.</small>
                    </div>
                    <span class="badge bg-secondary">{{ $recentAssignments->count() }} records</span>
                </div>

                @if($recentAssignments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        No assignment record available yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Worker</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Primary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAssignments as $assignment)
                                    <tr>
                                        <td class="small text-muted">{{ $assignment->assignment_type ?? 'Care Worker' }}</td>
                                        <td class="small">
                                            <strong>{{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}</strong><br>
                                            <span class="text-muted small">{{ $assignment->worker->role_type ?? 'Worker' }}</span>
                                        </td>
                                        <td class="small">
                                            {{ $assignment->start_date?->format('d M Y') ?? 'TBC' }}
                                            @if($assignment->end_date)
                                                &ndash; {{ $assignment->end_date->format('d M Y') }}
                                            @else
                                                &ndash; ongoing
                                            @endif
                                        </td>
                                        <td class="small text-uppercase fw-semibold">{{ $assignment->status }}</td>
                                        <td class="small">{{ $assignment->is_primary ? 'Yes' : 'No' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
