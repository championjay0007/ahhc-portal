@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Edit participant</h4>
                <p class="text-muted mb-0">Update the participant profile and portal access settings.</p>
            </div>
            <a href="{{ route('portal.admin.participants.show', $participant) }}" class="btn btn-sm btn-outline-secondary">Back to details</a>
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
                <form method="POST" action="{{ route('portal.admin.participants.update', $participant) }}">
                    @csrf
                    @method('PUT')

                    @include('admin.participants._form', ['participant' => $participant, 'supportPeople' => $supportPeople])

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
