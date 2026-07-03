@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Add participant</h4>
                <p class="text-muted mb-0">Create a new participant profile and portal user account.</p>
            </div>
            <a href="{{ route('portal.admin.participants') }}" class="btn btn-sm btn-outline-secondary">Back to list</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('portal.admin.participants.store') }}">
                    @csrf

                    @include('admin.participants._form', ['supportPeople' => $supportPeople, 'participant' => new \App\Models\Participant])

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Create participant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
