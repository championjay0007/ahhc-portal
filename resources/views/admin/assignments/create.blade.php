@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">New Assignment</h4>
                <p class="text-muted mb-0">Assign a worker to a participant with dates, type and support details.</p>
            </div>
            <a href="{{ route('portal.admin.assignments') }}" class="btn btn-sm btn-outline-secondary">Back to assignments</a>
        </div>

        <div class="card p-4">
            <form method="POST" action="{{ route('portal.admin.assignments.store') }}">
                @csrf

                @include('admin.assignments._form', [
                    'buttonText' => 'Create assignment',
                    'assignment' => null,
                ])
            </form>
        </div>
    </div>
@endsection
