@extends('layouts.portal')

@section('title', 'Sent Messages')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">
                <i class="bi bi-send-fill"></i> Sent Messages
            </h1>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($messages->count() > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>To</th>
                                <th>Subject</th>
                                <th>Date Sent</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                            <tr>
                                <td>{{ $message->recipient->name }}</td>
                                <td>{{ Str::limit($message->subject, 50) }}</td>
                                <td>{{ $message->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route($messageRoutePrefix.'show', $message) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-send"></i> You haven't sent any messages yet.
        </div>
    @endif
</div>
@endsection
