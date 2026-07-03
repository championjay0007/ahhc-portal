@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Add Worker</h4>
                <p class="text-muted mb-0">Create a new worker account and profile for admin assignment.</p>
            </div>
            <a href="{{ route('portal.admin.workers') }}" class="btn btn-sm btn-outline-secondary">Back to workers</a>
        </div>

        <div class="card p-4">
            <form method="POST" action="{{ route('portal.admin.workers.store') }}">
                @csrf

                @include('admin.workers._form', [
                    'buttonText' => 'Create worker',
                    'showPasswordFields' => true,
                ])
            </form>
        </div>
    </div>
@endsection
