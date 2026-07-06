@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Invoice {{ $invoice->invoice_number }}</h4>
                <p class="text-muted mb-0">Review invoice details and mark as reviewed.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.invoices') }}" class="btn btn-sm btn-outline-secondary">Back to invoices</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Invoice details</h5>
                        <p class="mb-1"><strong>Participant:</strong> {{ optional($invoice->participant)->first_name }} {{ optional($invoice->participant)->last_name }}</p>
                        <p class="mb-1"><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
                        <p class="mb-1"><strong>Invoice date:</strong> {{ optional($invoice->invoice_date)->format('Y-m-d') }}</p>
                        <p class="mb-1"><strong>Service date:</strong> {{ optional($invoice->service_date)->format('Y-m-d') ?? '—' }}</p>
                        <p class="mb-1"><strong>Linked pre-approval:</strong>
                            @if($invoice->preApprovalRequest)
                                <a href="{{ route('portal.admin.pre_approvals.show', $invoice->preApprovalRequest) }}">{{ $invoice->preApprovalRequest->request_number }}</a>
                            @else
                                <span class="text-warning">Not linked</span>
                            @endif
                        </p>
                        <p class="mb-1"><strong>Due date:</strong> {{ optional($invoice->due_date)->format('Y-m-d') }}</p>
                        <p class="mb-1"><strong>Approved at:</strong> {{ optional($invoice->approved_at)->format('Y-m-d H:i') ?? '—' }}</p>
                        <p class="mb-1"><strong>Approved by:</strong> {{ optional($invoice->approver)->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Amount</h5>
                        <p class="mb-1"><strong>Amount:</strong> ${{ number_format(($invoice->amount_cents ?? 0) / 100, 2) }}</p>
                        <p class="mb-1"><strong>Paid at:</strong> {{ optional($invoice->paid_at)->format('Y-m-d H:i') ?? '—' }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <h5>Notes</h5>
                    <p class="text-muted">{{ $invoice->notes ?? 'No notes provided.' }}</p>
                </div>

                @if($invoice->invoice_file_path || $invoice->attachment_path)
                    <div class="mb-4">
                        <h5>Attachment</h5>
                        <div class="alert alert-info">
                            <a href="{{ route('portal.admin.invoices.attachment.download', $invoice) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-download me-1"></i>Download invoice document
                            </a>
                        </div>
                    </div>
                @endif

                @if(! $invoice->pre_approval_id)
                    <div class="alert alert-warning">This invoice is not linked to a pre-approval.</div>
                @endif

                @php $relatedCareNotes = $invoice->careNotes()->count(); @endphp
                @if(! $relatedCareNotes)
                    <div class="alert alert-warning">No care note found for the invoice service date.</div>
                @endif

                @if($invoice->status === 'approved')
                    <form method="POST" action="{{ route('portal.admin.invoices.pay', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Mark as paid</button>
                    </form>
                    <button class="btn btn-danger ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#rejectInvoiceForm" aria-expanded="false" aria-controls="rejectInvoiceForm">
                        Reject invoice
                    </button>
                @elseif($invoice->status === 'paid')
                    <div class="alert alert-success">This invoice has already been paid.</div>
                @elseif($invoice->status === 'rejected')
                    <div class="alert alert-danger">This invoice was rejected.</div>
                    @if($invoice->rejection_reason)
                        <div class="alert alert-secondary mt-3">
                            <strong>Rejection reason:</strong>
                            <p class="mb-0">{{ $invoice->rejection_reason }}</p>
                        </div>
                    @endif
                @else
                    <form method="POST" action="{{ route('portal.admin.invoices.review', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve invoice</button>
                    </form>
                    <button class="btn btn-danger ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#rejectInvoiceForm" aria-expanded="false" aria-controls="rejectInvoiceForm">
                        Reject invoice
                    </button>
                @endif

                <div class="collapse mt-4" id="rejectInvoiceForm">
                    <div class="card card-body bg-light">
                        <form method="POST" action="{{ route('portal.admin.invoices.reject', $invoice) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label">Rejection reason</label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="4" class="form-control" placeholder="Explain why the invoice was rejected" required>{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger">Confirm reject invoice</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
