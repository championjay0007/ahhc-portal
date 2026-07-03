@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Pre-approvals</h2>
            <p class="text-muted">Submit requests for services and monitor their approval status.</p>
        </div>
    </div>

    <div class="card portal-card mb-4 p-4">
        <h5 class="mb-3">Request a new pre-approval</h5>
        <form method="POST" action="{{ route('portal.participant.pre_approvals.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Service type</label>
                    <input type="text" name="service_type" class="form-control" value="{{ old('service_type') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Service category</label>
                    <input type="text" name="service_category" class="form-control" value="{{ old('service_category') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Requested amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="requested_amount" class="form-control" value="{{ old('requested_amount') }}" min="0.01" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Worker</label>
                    @if(isset($workers) && $workers->isNotEmpty())
                        <select name="worker_id" class="form-select">
                            <option value="">Choose assigned worker</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('worker_id') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->first_name }} {{ $worker->last_name }}
                                    @if($worker->compliance_expiry_at && $worker->compliance_expiry_at->isPast())
                                        (compliance expired)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="worker_id" class="form-control" value="{{ old('worker_id') }}" placeholder="Worker ID">
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supplier (optional)</label>
                    @if(isset($workers) && $workers->isNotEmpty())
                        <select name="supplier_id" class="form-select">
                            <option value="">Choose supplier if applicable</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('supplier_id') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->first_name }} {{ $worker->last_name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="supplier_id" class="form-control" value="{{ old('supplier_id') }}" placeholder="Supplier ID">
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expiry date</label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quote or supporting document</label>
                    <input type="file" name="quote" class="form-control" required>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">Submit request</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card portal-card p-4">
        <h5 class="mb-3">Request history</h5>

        @if($requests->isEmpty())
            <p class="text-muted">No pre-approval requests found.</p>
        @else
            <div class="list-group">
                @foreach($requests as $request)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $request->request_number }}</strong>
                                <div class="small text-muted">{{ $request->service_category ?? $request->service_type }} • {{ $request->status_label ?? ucfirst(str_replace('_', ' ', $request->status)) }}</div>
                            </div>
                            <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'approved_with_conditions' ? 'info' : ($request->status === 'rejected' ? 'danger' : ($request->status === 'info_requested' ? 'warning' : 'secondary'))) }}">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span>
                        </div>
                        <p class="mt-2 mb-1 small text-muted">{{ \Illuminate\Support\Str::limit($request->description, 120) }}</p>
                        <div class="small text-muted">From {{ optional($request->start_date)->format('Y-m-d') }} to {{ optional($request->end_date)->format('Y-m-d') }}</div>
                        @if($request->expiry_date)
                            <div class="small text-muted">Expiry date: {{ $request->expiry_date->format('Y-m-d') }}</div>
                        @endif
                        @if($request->quote_file_path)
                            <a href="{{ route('portal.participant.pre_approvals.quote.download', $request) }}" class="btn btn-sm btn-outline-secondary mt-2">Download quote</a>
                        @endif
                        @if($request->attachments->isNotEmpty())
                            <div class="mt-2">
                                @foreach($request->attachments as $attachment)
                                    <a href="{{ route('portal.participant.pre_approvals.attachments.download', [$request, $attachment]) }}" class="btn btn-sm btn-outline-secondary me-2">{{ $attachment->title ?? 'Attachment' }}</a>
                                @endforeach
                            </div>
                        @endif
                        @if($request->status === 'approved')
                            <div class="small text-success mt-2">Committed amount: ${{ number_format(($request->committed_amount_cents ?? $request->requested_amount_cents) / 100, 2) }}</div>
                        @endif
                        @if(in_array($request->status, ['info_requested', 'rejected', 'cancelled']) && $request->review_notes)
                            <div class="small text-warning mt-2">Message from admin: {{ \Illuminate\Support\Str::limit($request->review_notes, 150) }}</div>
                        @endif
                        @if($request->comments->isNotEmpty())
                            <div class="mt-3">
                                <strong class="small text-muted">Conversation</strong>
                                <ul class="list-unstyled small mb-0">
                                    @foreach($request->comments as $comment)
                                        <li class="border rounded p-2 mb-2">
                                            <div><strong>{{ optional($comment->commenter)->name ?? 'System' }}</strong> <span class="text-muted">{{ $comment->created_at->diffForHumans() }}</span></div>
                                            <div>{{ $comment->message }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if($request->status === 'info_requested')
                            <form method="POST" action="{{ route('portal.participant.pre_approvals.update', $request) }}" enctype="multipart/form-data" class="mt-3">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Response to admin request</label>
                                    <textarea name="participant_note" class="form-control" rows="3" required>{{ old('participant_note') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload supporting document</label>
                                    <input type="file" name="quote" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Send response</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
