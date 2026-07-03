@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Invoices</h2>
            <p class="text-muted">Track bills, due dates and totals for your provider services.</p>
        </div>
    </div>

    <div class="card portal-card mb-4 p-4">
        <h5 class="mb-3">Submit a new invoice</h5>
        <form method="POST" action="{{ route('portal.participant.invoices.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Invoice number</label>
                    <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice date</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Service date</label>
                    <input type="date" name="service_date" class="form-control" value="{{ old('service_date') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Amount ($)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" min="0.01" required>
                    <small class="text-muted">Enter the invoice amount in dollars, e.g. 1500.00</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pre-approval</label>
                    <select name="pre_approval_id" class="form-select">
                        <option value="">None</option>
                        @foreach($preApprovals as $preApproval)
                            <option value="{{ $preApproval->id }}" @selected(old('pre_approval_id') == $preApproval->id)>
                                {{ $preApproval->request_number }} — ${{ number_format(($preApproval->committed_amount_cents ?? $preApproval->requested_amount_cents) / 100, 2) }}
                                ({{ ucfirst($preApproval->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Attachment (Invoice document, receipt, etc.)</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xlsx,.xls">
                    <small class="text-muted">PDF, JPG, PNG, DOC, DOCX, or Excel files up to 10MB</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Submit invoice</button>
        </form>
    </div>

    <div class="card portal-card p-4">
        <h5 class="mb-3">Submitted invoices</h5>
        @if($invoices->isEmpty())
            <p class="text-muted">No invoices submitted yet.</p>
        @else
            <div class="list-group">
                @foreach($invoices as $invoice)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $invoice->invoice_number }}</strong>
                                <div class="small text-muted">{{ $invoice->invoice_date->format('Y-m-d') }} • {{ ucfirst($invoice->status) }}</div>
                                @if($invoice->invoice_file_path || $invoice->attachment_path)
                                    <div class="mt-1">
                                        <a href="{{ route('portal.participant.invoices.download', $invoice->id) }}" class="btn btn-xs btn-outline-secondary">
                                            <i class="bi bi-download me-1"></i>Download attachment
                                        </a>
                                    </div>
                                @endif
                                @if($invoice->preApprovalRequest)
                                    <div class="small text-muted mt-2">
                                        Linked pre-approval: <strong>{{ $invoice->preApprovalRequest->request_number }}</strong>
                                    </div>
                                @endif
                            </div>
                            <span class="fw-bold">${{ number_format($invoice->amount_cents / 100, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
