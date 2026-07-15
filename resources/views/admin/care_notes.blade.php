@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Care Notes</h4>
                <p class="text-muted mb-0">Review care notes created for participants.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if($careNotes->isEmpty())
                    <p class="text-muted">No care notes found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Shift Date</th>
                                    <th>Participant</th>
                                    <th>Worker</th>
                                    <th>Tasks</th>
                                    <th>Risk</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($careNotes as $note)
                                    <tr>
                                        <td>{{ optional($note->shift_date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($note->participant)->first_name }} {{ optional($note->participant)->last_name }}</td>
                                        <td>{{ optional($note->worker)->first_name ?? '—' }} {{ optional($note->worker)->last_name ?? '' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($note->tasks_completed, 80) }}</td>
                                        <td>{!! $note->risks_flag ? '<span class="text-danger">Yes</span>' : '<span class="text-muted">No</span>' !!}</td>
                                        <td>{{ ucfirst($note->status) }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('portal.admin.care_notes.show', $note) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                                <form method="POST" action="{{ route('portal.admin.care_notes.destroy', $note) }}" class="d-inline" onsubmit="return confirm('Delete this care note?');">
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
                        {{ $careNotes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
