@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Edit Worker</h4>
                <p class="text-muted mb-0">Update worker details and compliance information.</p>
            </div>
            <a href="{{ route('portal.admin.workers.show', $worker) }}" class="btn btn-sm btn-outline-secondary">Back to profile</a>
        </div>

        <div class="card p-4">
            <form method="POST" action="{{ route('portal.admin.workers.update', $worker) }}">
                @csrf
                @method('PUT')

                @include('admin.workers._form', [
                    'buttonText' => 'Save changes',
                    'showPasswordFields' => true,
                ])
            </form>
        </div>
    </div>
@endsection
