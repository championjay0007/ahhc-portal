@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Care Notes</h2>
            <p class="text-muted">Record and review care notes related to your participant account.</p>
        </div>
    </div>

    <!-- Participants cannot create care notes; workers submit care notes. -->

    @if(! empty($missingEvidence) && count($missingEvidence) > 0)
        <div class="card portal-card mb-4 p-4 border-warning">
            <h5 class="mb-2">Missing Service Evidence</h5>
            <p class="text-muted small">The following assigned workers have not uploaded service evidence in the last 30 days.</p>
            <ul class="mb-0">
                @foreach($missingEvidence as $me)
                    <li>{{ $me['worker_name'] ?? 'Worker' }} @if($me['start_date']) - from {{ optional($me['start_date'])->format('Y-m-d') }}@endif</li>
                @endforeach
            </ul>
            <p class="mt-2"><a href="{{ route('portal.participant.documents.index') }}" class="link-primary">Upload or request evidence</a></p>
        </div>
    @endif

    <div class="card portal-card mb-4 p-4">
        <h5 class="mb-3">Monthly Care Management Checklist</h5>
        <p class="text-muted small">Complete this checklist each month to confirm key care management tasks have been completed.</p>
        <form method="POST" action="{{ route('portal.participant.checklist.store') }}">
            @csrf
            <div class="mb-3 form-check">
                <input type="checkbox" name="items[]" value="Medication review" class="form-check-input" id="chk_medication">
                <label class="form-check-label" for="chk_medication">Medication review completed</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="items[]" value="Behaviour review" class="form-check-input" id="chk_behaviour">
                <label class="form-check-label" for="chk_behaviour">Behaviour review completed</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="items[]" value="Equipment check" class="form-check-input" id="chk_equipment">
                <label class="form-check-label" for="chk_equipment">Equipment check completed</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="items[]" value="Incident review" class="form-check-input" id="chk_incident">
                <label class="form-check-label" for="chk_incident">Incident review completed</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="items[]" value="Service evidence collected" class="form-check-input" id="chk_evidence">
                <label class="form-check-label" for="chk_evidence">Service evidence collected</label>
            </div>
            <button type="submit" class="btn btn-outline-primary">Save checklist</button>
        </form>
    </div>

    <div class="card portal-card p-4">
        <h5 class="mb-3">Care note history</h5>
        @if($notes->isEmpty())
            <p class="text-muted">No care notes have been recorded yet.</p>
        @else
            <div class="list-group">
                @foreach($notes as $note)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ optional($note->shift_date)->format('Y-m-d') }}</strong>
                                <div class="small text-muted">{{ $note->service_type ?? 'General care note' }}</div>
                            </div>
                            <span class="badge bg-{{ $note->status === 'approved' ? 'success' : ($note->status === 'rejected' ? 'danger' : 'secondary') }}">{{ ucfirst($note->status) }}</span>
                        </div>
                        <p class="mt-2 mb-0">{{ \Illuminate\Support\Str::limit($note->care_summary, 140) }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
