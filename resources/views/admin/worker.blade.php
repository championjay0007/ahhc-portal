@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Worker: {{ $worker->first_name }} {{ $worker->last_name }}</h4>
                <p class="text-muted mb-0">{{ $worker->worker_number }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.admin.workers') }}" class="btn btn-sm btn-outline-secondary">All workers</a>
                <a href="{{ route('portal.admin.assignments') }}" class="btn btn-sm btn-outline-secondary">View assignments</a>
                <a href="{{ route('portal.admin.workers.edit', $worker) }}" class="btn btn-sm btn-primary">Edit</a>
                @if($worker->user)
                    <form method="POST" action="{{ route('portal.admin.users.dashboard.login', $worker->user) }}" class="d-inline-block">
                        @csrf
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-success">Force dashboard login</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('portal.admin.workers.destroy', $worker) }}" class="d-inline-block" onsubmit="return confirm('Delete this worker?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card p-3 mb-3">
                    <h6>Profile</h6>
                    <p class="mb-1"><strong>Worker #</strong> {{ $worker->worker_number }}</p>
                    <p class="mb-1"><strong>Email</strong> {{ $worker->email }}</p>
                    <p class="mb-1"><strong>Phone</strong> {{ $worker->phone }}</p>
                    <p class="mb-1"><strong>Status</strong> {{ ucfirst($worker->status) }}</p>
                    <p class="mb-1"><strong>Qualification</strong> {{ $worker->qualification ?? '—' }}</p>
                    <p class="mb-1"><strong>Vehicle</strong> {{ $worker->vehicle_type ?? '—' }}</p>
                    <p class="mb-1"><strong>Compliance expiry</strong> {{ optional($worker->compliance_expiry_at)->format('Y-m-d') ?? 'Not set' }}</p>
                    <p class="mb-1"><strong>Background check</strong> {{ optional($worker->background_check_expiry_at)->format('Y-m-d') ?? 'Not set' }}</p>
                </div>

                <div class="card p-3 mb-3">
                    <h6>Assign onboarding form</h6>
                    <p class="small text-muted mb-3">Pending worker documents: {{ $pendingDocumentsCount }}</p>
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('portal.admin.documents.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="owner_type" value="worker">
                        <input type="hidden" name="owner_id" value="{{ $worker->id }}">

                        <div class="mb-3">
                            <label for="title" class="form-label">Form title</label>
                            <input id="title" name="title" type="text" class="form-control" value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="document_type" class="form-label">Form type</label>
                            <select id="document_type" name="document_type" class="form-select" required>
                                @foreach($documentTypes as $type => $label)
                                    <option value="{{ $type }}"{{ old('document_type') === $type ? ' selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Form file</label>
                            <input id="file" name="file" type="file" class="form-control" required>
                            <div class="form-text">Accepted formats: PDF, JPG, PNG, DOC, DOCX, TXT.</div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">Assign form</button>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card p-3 mb-3">
                    <h6>Assignments</h6>
                    @if($worker->assignments->isEmpty())
                        <p class="text-muted mb-0">No active assignments.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($worker->assignments as $assignment)
                                <li class="list-group-item">
                                    <strong>{{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</strong>
                                    <div class="small text-muted">Started {{ optional($assignment->start_date)->format('Y-m-d') }} | Status: {{ ucfirst($assignment->status) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card p-3 mb-3">
                            <h6>Care notes</h6>
                            <p class="mb-0 fw-bold">{{ $worker->careNotes->count() }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3 mb-3">
                            <h6>Incidents</h6>
                            <p class="mb-0 fw-bold">{{ $worker->incidents->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-3 mb-3">
                    <h6>Recent notes & invoices</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="small text-muted">Recent invoices</h6>
                            <ul class="list-group list-group-flush">
                                @forelse($worker->invoices->take(5) as $invoice)
                                    <li class="list-group-item">
                                        <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                                        <div class="small text-muted">{{ ucfirst($invoice->status) }} | {{ optional($invoice->invoice_date)->format('Y-m-d') }}</div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No invoices found.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="small text-muted">Recent care notes</h6>
                            <ul class="list-group list-group-flush">
                                @forelse($worker->careNotes->take(5) as $note)
                                    <li class="list-group-item">
                                        <div class="fw-semibold">{{ optional($note->shift_date)->format('Y-m-d') }}</div>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($note->care_summary ?? '—', 100) }}</div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No care notes recorded.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
