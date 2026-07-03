@extends('layouts.portal')

@section('title', 'Submit Invoice')

@section('content')
    <div class="portal-page-header">
        <h1>Submit Invoice</h1>
        <p>Send a supplier invoice for services you provided to your assigned participant.</p>
    </div>

    <div class="card p-4 mb-4">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.worker.invoices.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="participant_id">Participant</label>
                    <select id="participant_id" name="participant_id" class="form-control" required>
                        <option value="">Select assigned participant</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->participant->id }}" @selected(old('participant_id') == $assignment->participant->id)>
                                {{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="invoice_number">Invoice number</label>
                    <input id="invoice_number" name="invoice_number" type="text" class="form-control" value="{{ old('invoice_number') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="invoice_date">Invoice date</label>
                    <input id="invoice_date" name="invoice_date" type="date" class="form-control" value="{{ old('invoice_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="service_date">Service date</label>
                    <input id="service_date" name="service_date" type="date" class="form-control" value="{{ old('service_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="due_date">Due date</label>
                    <input id="due_date" name="due_date" type="date" class="form-control" value="{{ old('due_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="amount">Amount ($)</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="form-control" value="{{ old('amount') }}" required>
                    <small class="text-muted">Enter the invoice amount in dollars, e.g. 1500.00</small>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="attachment">Invoice attachment</label>
                    <input id="attachment" name="attachment" type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xlsx,.xls" required>
                    <small class="text-muted">PDF, image or document file up to 10MB.</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Submit invoice</button>
        </form>
    </div>

    <div class="card p-4">
        <h5 class="mb-3">Recent invoices</h5>

        @if($invoices->isEmpty())
            <p class="text-muted">No invoices submitted yet.</p>
        @else
            <div class="list-group">
                @foreach($invoices as $invoice)
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $invoice->invoice_number }}</strong>
                            <div class="small text-muted">{{ $invoice->invoice_date->format('Y-m-d') }} • {{ ucfirst($invoice->status) }}</div>
                            <div class="small text-muted">Participant: {{ $invoice->participant->first_name }} {{ $invoice->participant->last_name }}</div>
                            <div class="small text-muted">Amount: ${{ number_format($invoice->amount_cents / 100, 2) }}</div>
                        </div>
                        <div class="text-end">
                            @if($invoice->attachment_path)
                                <a href="{{ route('portal.worker.invoices.download', $invoice) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
