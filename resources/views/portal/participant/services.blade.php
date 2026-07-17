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
                            <p class="text-muted mb-1">Recent shifts</p>
                            <h5 class="mb-0">{{ $recentShifts->count() }}</h5>
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

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card portal-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h5 class="mb-1">Recent Care Notes</h5>
                        <small class="text-muted">Worker-submitted notes from your recent visits.</small>
                    </div>
                    <a href="{{ route('portal.participant.care_notes.index') }}" class="btn btn-sm btn-outline-primary">View all care notes</a>
                </div>

                @if($recentCareNotes->isEmpty())
                    <div class="text-center py-4 text-muted">
                        No care notes have been recorded yet.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentCareNotes as $note)
                            <a href="{{ route('portal.participant.care_notes.show', $note) }}" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <div>
                                        <strong>{{ $note->shift_date?->format('d M Y') ?? 'No date' }}</strong>
                                        <div class="small text-muted">{{ $note->service_type ?? 'General care note' }}</div>
                                        <div class="small text-muted">Worker: {{ optional($note->worker)->first_name }} {{ optional($note->worker)->last_name }}</div>
                                    </div>
                                    <span class="badge bg-{{ $note->status === 'approved' ? 'success' : ($note->status === 'rejected' ? 'danger' : 'secondary') }}">{{ ucfirst($note->status) }}</span>
                                </div>
                                <p class="mt-2 mb-0 small text-truncate">{{ \Illuminate\Support\Str::limit($note->care_summary, 120) }}</p>
                            </a>
                        @endforeach
                    </div>
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
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary">{{ $recentShifts->count() }} records</span>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createShiftModal">Create Shift</button>
                    </div>
                </div>

                @if($recentShifts->isEmpty())
                    <div class="text-center py-5 text-muted">
                        No shift bookings available yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Worker</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentShifts as $shift)
                                    <tr>
                                        <td class="small text-muted">{{ $shift->service_type ?? 'Service' }}</td>
                                        <td class="small">
                                            <strong>{{ $shift->worker?->first_name ?? 'Worker' }} {{ $shift->worker?->last_name ?? '' }}</strong><br>
                                            <span class="text-muted small">{{ $shift->worker?->role_type ?? 'Worker' }}</span>
                                        </td>
                                        <td class="small">{{ $shift->shift_date?->format('d M Y') ?? 'TBC' }}</td>
                                        <td class="small">{{ $shift->start_time }} &ndash; {{ $shift->end_time }}</td>
                                        <td class="small text-uppercase fw-semibold">{{ ucfirst(str_replace('_', ' ', $shift->status)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="modal fade" id="createShiftModal" tabindex="-1" aria-labelledby="createShiftModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('portal.participant.services.shifts.create') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createShiftModalLabel">Create Shift</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="worker_id">Worker</label>
                            <select class="form-select" id="worker_id" name="worker_id" required>
                                <option value="">Select a worker</option>
                                @foreach($participant->assignments()->where('status', 'active')->with('worker')->get() as $assignment)
                                    @if($assignment->worker)
                                        <option value="{{ $assignment->worker->id }}">{{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="shift_date">Date</label>
                                <input type="date" class="form-control" id="shift_date" name="shift_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label" for="start_time">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="end_time">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label" for="service_type">Service Type</label>
                            <input type="text" class="form-control" id="service_type" name="service_type" placeholder="e.g. Personal Care">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="service_category">Service Category</label>
                            <input type="text" class="form-control" id="service_category" name="service_category" placeholder="e.g. Domestic Assistance">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="Enter location">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
