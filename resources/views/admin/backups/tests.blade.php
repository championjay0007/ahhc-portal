@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Disaster Recovery Tests</h2>
            <p class="text-muted mb-0">Monitor the status of disaster recovery exercises and compliance readiness.</p>
        </div>
        <a href="{{ route('portal.admin.backups.tests.create') }}" class="btn btn-primary">Record Test</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Test date</th>
                            <th>Status</th>
                            <th>Conducted by</th>
                            <th>Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tests as $test)
                            <tr>
                                <td>{{ $test->test_date->format('Y-m-d H:i') }}</td>
                                <td>{{ $test->status }}</td>
                                <td>{{ $test->conductedBy?->name ?? 'system' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($test->summary, 100) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No disaster recovery tests recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
