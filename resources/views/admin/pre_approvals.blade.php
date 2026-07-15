@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Pre-approvals</h4>
                <p class="text-muted mb-0">Review submitted pre-approval requests.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if($requests->isEmpty())
                    <p class="text-muted">No pre-approval requests found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Participant</th>
                                    <th>Service</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td>{{ $request->request_number }}</td>
                                        <td>{{ optional($request->participant)->first_name }} {{ optional($request->participant)->last_name }}</td>
                                        <td>{{ $request->service_category }}</td>
                                        <td>${{ number_format(($request->requested_amount_cents ?? 0) / 100, 2) }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>{{ optional($request->submitted_at)->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('portal.admin.pre_approvals.show', $request) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                                <form method="POST" action="{{ route('portal.admin.pre_approvals.destroy', $request) }}" class="d-inline" onsubmit="return confirm('Delete this pre-approval request?');">
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
                        {{ $requests->links() ?? '' }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
