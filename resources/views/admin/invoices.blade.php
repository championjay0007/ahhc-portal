@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Invoices</h4>
                <p class="text-muted mb-0">Review submitted invoices from participants.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if($invoices->isEmpty())
                    <p class="text-muted">No invoices have been submitted yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Participant</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Invoice date</th>
                                    <th>Due date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ optional($invoice->participant)->first_name }} {{ optional($invoice->participant)->last_name }}</td>
                                        <td>{{ ucfirst($invoice->status) }}</td>
                                        <td>${{ number_format(($invoice->amount_cents ?? 0) / 100, 2) }}</td>
                                        <td>{{ optional($invoice->invoice_date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($invoice->due_date)->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('portal.admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                                <form method="POST" action="{{ route('portal.admin.invoices.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('Delete this invoice?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $invoices->links() ?? '' }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
