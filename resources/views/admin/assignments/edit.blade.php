@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Edit Assignment</h4>
                <p class="text-muted mb-0">Update assignment details, dates, and coverage.</p>
            </div>
            <a href="{{ route('portal.admin.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-secondary">Back to assignment</a>
        </div>

        <div class="card p-4">
            <form method="POST" action="{{ route('portal.admin.assignments.update', $assignment) }}">
                @csrf
                @method('PUT')

                @include('admin.assignments._form', [
                    'buttonText' => 'Save changes',
                    'assignment' => $assignment,
                ])
            </form>
        </div>
    </div>
@endsection
