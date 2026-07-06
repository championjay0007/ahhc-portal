@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Pre-approval {{ $request->request_number }}</h4>
                <p class="text-muted mb-0">Review and act on this request.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.pre_approvals') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5>Request details</h5>
                <dl class="row">
                    <dt class="col-sm-3">Participant</dt>
                    <dd class="col-sm-9">{{ optional($request->participant)->first_name }} {{ optional($request->participant)->last_name }}</dd>

                    <dt class="col-sm-3">Service type</dt>
                    <dd class="col-sm-9">{{ $request->service_type }}</dd>

                    <dt class="col-sm-3">Service category</dt>
                    <dd class="col-sm-9">{{ $request->service_category }}</dd>

                    <dt class="col-sm-3">Supplier / worker</dt>
                    <dd class="col-sm-9">{{ optional($request->supplier)->first_name }} {{ optional($request->supplier)->last_name }}{{ $request->worker ? ' (' . $request->worker->first_name . ' ' . $request->worker->last_name . ')' : '' }}</dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $request->description }}</dd>

                    <dt class="col-sm-3">Requested amount</dt>
                    <dd class="col-sm-9">${{ number_format(($request->requested_amount_cents ?? 0) / 100, 2) }}</dd>

                    <dt class="col-sm-3">Estimated amount</dt>
                    <dd class="col-sm-9">${{ number_format((($request->estimated_amount_cents ?? $request->requested_amount_cents) ?? 0) / 100, 2) }}</dd>

                    <dt class="col-sm-3">Committed amount</dt>
                    <dd class="col-sm-9">${{ number_format((($request->committed_amount_cents ?? $request->requested_amount_cents) ?? 0) / 100, 2) }}</dd>

                    <dt class="col-sm-3">Start date</dt>
                    <dd class="col-sm-9">{{ optional($request->start_date)->format('Y-m-d') ?? '—' }}</dd>

                    <dt class="col-sm-3">End date</dt>
                    <dd class="col-sm-9">{{ optional($request->end_date)->format('Y-m-d') ?? '—' }}</dd>

                    <dt class="col-sm-3">Expiry date</dt>
                    <dd class="col-sm-9">{{ optional($request->expiry_date)->format('Y-m-d') ?? '—' }}</dd>

                    <dt class="col-sm-3">Quote document</dt>
                    <dd class="col-sm-9">
                        @if($request->quote_file_path)
                            <a href="{{ route('portal.admin.pre_approvals.quote.download', $request) }}">Download quote</a>
                        @else
                            <span class="text-muted">No quote uploaded</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</dd>

                    <dt class="col-sm-3">Submitted</dt>
                    <dd class="col-sm-9">{{ optional($request->submitted_at)->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-3">Admin reviewer</dt>
                    <dd class="col-sm-9">{{ optional($request->approver)->name ?? '—' }}</dd>

                    <dt class="col-sm-3">Decision reason</dt>
                    <dd class="col-sm-9">{{ $request->decision_reason ?? 'No reason provided.' }}</dd>

                    <dt class="col-sm-3">Budget / Care plan</dt>
                    <dd class="col-sm-9">
                        <a href="{{ route('portal.admin.participants.show', $request->participant_id) }}">View participant budget / care plan</a>
                    </dd>
                </dl>
            </div>
        </div>

        @if(isset($budgetMetrics))
            <div class="card mb-4">
                <div class="card-body">
                    <h6>System review checks</h6>
                    <ul class="mb-0">
                        <li><strong>Quarter budget total:</strong> ${{ number_format($budgetMetrics['total'] / 100, 2) }}</li>
                        <li><strong>Committed:</strong> ${{ number_format($budgetMetrics['committed'] / 100, 2) }}</li>
                        <li><strong>Approved spend:</strong> ${{ number_format($budgetMetrics['approved'] / 100, 2) }}</li>
                        @php
                            $bmTotal = $budgetMetrics['total'] ?? $budgetMetrics['total_available'] ?? 0;
                            $bmUsed = $budgetMetrics['used'] ?? (($budgetMetrics['committed'] ?? 0) + ($budgetMetrics['approved'] ?? 0) + ($budgetMetrics['paid'] ?? 0));
                            $bmRemaining = (int) ($bmTotal - $bmUsed);
                        @endphp
                        <li><strong>Remaining balance:</strong> ${{ number_format($bmRemaining / 100, 2) }}</li>
                    </ul>

                    @if(! $budgetAvailable)
                        <div class="alert alert-danger mt-3 mb-0">Budget availability check failed: there is not enough remaining budget to commit this amount.</div>
                    @endif

                    @if(!empty($carePlanWarnings))
                        <div class="alert alert-warning mt-3 mb-0">
                            <strong>Care plan alignment issues:</strong>
                            <ul class="mb-0">
                                @foreach($carePlanWarnings as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($workerComplianceNotes))
                        <div class="alert alert-warning mt-3 mb-0">
                            <strong>Worker compliance issues:</strong>
                            <ul class="mb-0">
                                @foreach($workerComplianceNotes as $note)
                                    <li>{{ $note }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <h5>Attachments</h5>
                @if($request->attachments->isEmpty())
                    <p class="text-muted">No additional attachments.</p>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($request->attachments as $attachment)
                            <a href="{{ route('portal.admin.pre_approvals.attachments.download', [$request, $attachment]) }}" class="btn btn-sm btn-outline-secondary">{{ $attachment->title ?? 'Attachment' }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5>Comment history</h5>
                @if($request->comments->isEmpty())
                    <p class="text-muted">No comments yet.</p>
                @else
                    <div class="timeline">
                        @foreach($request->comments as $comment)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ optional($comment->commenter)->name ?? 'System' }}</strong>
                                        <span class="text-muted">• {{ ucfirst(str_replace('_', ' ', $comment->comment_type)) }}</span>
                                    </div>
                                    <div class="small text-muted">{{ $comment->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <div>{{ $comment->message }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Review notes</label>
                    <textarea class="form-control" rows="3" readonly>{{ $request->review_notes ?? 'No review notes yet.' }}</textarea>
                </div>

                @if(in_array($request->status, ['submitted', 'info_requested']))
                    <form method="POST" action="{{ route('portal.admin.pre_approvals.approve', $request) }}" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Committed amount</label>
                            <input type="text" name="committed_amount" value="{{ old('committed_amount', number_format(($request->requested_amount_cents ?? 0) / 100, 2)) }}" class="form-control" placeholder="e.g. 1500.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Condition notes</label>
                            <textarea name="condition_notes" class="form-control" rows="3">{{ old('condition_notes') }}</textarea>
                        </div>
                        <button type="submit" name="decision_type" value="approve" class="btn btn-success">Approve</button>
                        <button type="submit" name="decision_type" value="approve_with_conditions" class="btn btn-outline-primary">Approve with conditions</button>
                    </form>
                    <form method="POST" action="{{ route('portal.admin.pre_approvals.request_info', $request) }}" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Request more information</label>
                            <textarea name="review_notes" class="form-control" rows="3" required>{{ old('review_notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">Request info</button>
                    </form>
                    <form method="POST" action="{{ route('portal.admin.pre_approvals.reject', $request) }}" class="d-inline">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Rejection reason</label>
                            <textarea name="decision_reason" class="form-control" rows="3" required>{{ old('decision_reason') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Decline</button>
                    </form>
                @elseif(in_array($request->status, ['approved', 'approved_with_conditions']))
                    <div class="alert alert-success">
                        This request is {{ str_replace('_', ' ', $request->status) }} and can be cancelled if required.
                    </div>
                    <form method="POST" action="{{ route('portal.admin.pre_approvals.cancel', $request) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Cancel approval</button>
                    </form>
                @else
                    <div class="alert alert-info">This request was {{ str_replace('_', ' ', $request->status) }}.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
