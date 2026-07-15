@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Incidents</h4>
                <p class="text-muted mb-0">Manage incident and risk reports.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if($incidents->isEmpty())
                    <p class="text-muted">No incidents found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Participant</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incidents as $inc)
                                    <tr>
                                        <td>{{ optional($inc->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ optional($inc->participant)->first_name }} {{ optional($inc->participant)->last_name }}</td>
                                        <td>{{ ucfirst($inc->incident_type ?? $inc->category ?? '—') }}</td>
                                        <td>
                                            @if($inc->severity === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($inc->severity === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-secondary">Low</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($inc->status) }}</td>
                                        <td><a href="{{ route('portal.admin.incidents.show', $inc) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        @include('components.admin-pagination', ['paginator' => $incidents])
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
