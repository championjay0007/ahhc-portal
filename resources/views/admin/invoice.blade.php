@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Invoice {{ $invoice->invoice_number }}</h4>
                <p class="text-muted mb-0">Review the invoice details and confirm the approval decision.</p>
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
                        <p class="mb-1"><strong>Due date:</strong> {{ optional($invoice->due_date)->format('Y-m-d') }}</p>
                        <p class="mb-1"><strong>Approved at:</strong> {{ optional($invoice->approved_at)->format('Y-m-d H:i') ?? '—' }}</p>
                        <p class="mb-1"><strong>Approved by:</strong> {{ optional($invoice->approver)->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Amount</h5>
                        <p class="mb-1"><strong>Amount:</strong> ${{ number_format(($invoice->amount_cents ?? 0) / 100, 2) }}</p>
                        @if(in_array($invoice->status, ['approved', 'paid', 'rejected']) && $invoice->committed_amount_cents !== null)
                            <p class="mb-1"><strong>Committed amount:</strong> ${{ number_format($invoice->committed_amount_cents / 100, 2) }}</p>
                        @endif
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

                @php
                    $relatedCareNotes = $invoice->careNotes()->count();
                    $invoiceAmount = (float) (($invoice->amount_cents ?? $invoice->amount ?? 0) / 100);
                    $defaultCommittedAmount = number_format($invoiceAmount, 2, '.', '');
                @endphp
                @if(! $relatedCareNotes)
                    <div class="alert alert-warning">No care note found for the invoice service date.</div>
                @endif

                @if($invoice->status === 'approved')
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('portal.admin.invoices.pay', $invoice) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">Mark as paid</button>
                        </form>
                        <form method="POST" action="{{ route('portal.admin.invoices.reject', $invoice) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Reject invoice</button>
                        </form>
                    </div>
                @elseif($invoice->status === 'paid')
                    <div class="alert alert-success mb-0">This invoice has already been paid.</div>
                @elseif($invoice->status === 'rejected')
                    <div class="alert alert-danger mb-0">This invoice was rejected.</div>
                    @if($invoice->rejection_reason)
                        <div class="alert alert-secondary mt-3 mb-0">
                            <strong>Rejection reason:</strong>
                            <p class="mb-0">{{ $invoice->rejection_reason }}</p>
                        </div>
                    @endif
                @else
                    <div class="border rounded p-3 bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-8">
                                <form method="POST" action="{{ route('portal.admin.invoices.review', $invoice) }}" class="d-inline">
                                    @csrf
                                    <div class="mb-0">
                                        <label for="committed_amount" class="form-label fw-semibold">Committed amount</label>
                                        <input type="number" step="0.01" min="0" id="committed_amount" name="committed_amount" value="{{ old('committed_amount', $defaultCommittedAmount) }}" class="form-control" placeholder="e.g. 1500.00" required>
                                        <div class="form-text">This defaults to the invoice amount and can be edited before approval.</div>
                                    </div>

                                    <button type="submit" class="btn btn-primary mt-3">Approve invoice</button>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <form method="POST" action="{{ route('portal.admin.invoices.reject', $invoice) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100">Reject invoice</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
@endsection
