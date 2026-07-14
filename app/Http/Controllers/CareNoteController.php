<?php

namespace App\Http\Controllers;

use App\Models\CareNote;
use App\Models\Document;
use App\Models\Participant;
use App\Models\User;
use App\Notifications\CareNoteSubmitted;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class CareNoteController extends Controller
{
    public function indexForParticipant(Participant $participant)
    {
        $notes = $participant->careNotes()->latest()->get();

        return view('admin.participant.care_notes', compact('participant', 'notes'));
    }

    public function storeForParticipant(Participant $participant, Request $request)
    {
        $validated = $request->validate([
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'tasks_completed' => ['required', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'service_type' => ['nullable', 'string', 'max:100'],
            'risks_flag' => ['sometimes', 'boolean'],
            'service_confirmed' => ['accepted'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls', 'max:10240'],
        ]);

        $user = auth()->user();
        $workerId = $user->worker?->id ?? $participant->assignments()->where('status', 'active')->latest()->value('worker_id');

        if (! $workerId) {
            return redirect()->back()->withErrors(['worker' => 'No worker assigned to this participant. Care notes require a worker.']);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('care_notes', 'local');
        }

        $careNote = CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $workerId,
            'shift_date' => $validated['shift_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'tasks_completed' => $validated['tasks_completed'],
            'care_summary' => $validated['tasks_completed'],
            'observations' => $validated['observations'] ?? null,
            'risks_flag' => $request->boolean('risks_flag'),
            'service_confirmed' => true,
            'attachment_path' => $attachmentPath,
            'service_type' => $request->input('service_type'),
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_id' => $user->id,
        ]);

        AuditLogService::record('Care Note Create', $careNote, [], [
            'participant_id' => $careNote->participant_id,
            'worker_id' => $careNote->worker_id,
            'shift_date' => $careNote->shift_date,
            'status' => $careNote->status,
        ]);

        // Notify admins
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new CareNoteSubmitted($careNote));
            }
        } catch (\Exception $e) {
            // swallow notification errors to not block user flow
        }

        return redirect()->route('portal.admin.participants.care_notes', $participant)->with('status', 'Care note saved.');
    }

    public function index()
    {
        $user = auth()->user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();
        $notes = $participant->careNotes()->latest()->get();

        // Determine missing service evidence for active assignments (last 30 days)
        $assignments = $participant->assignments()->where('status', 'active')->get();
        $missingEvidence = [];
        foreach ($assignments as $assignment) {
            $workerId = $assignment->worker_id;
            $hasEvidence = Document::where('owner_type', Participant::class)
                ->where('owner_id', $participant->id)
                ->where('document_type', 'Service evidence')
                ->where('uploaded_by_id', $workerId)
                ->where('created_at', '>=', now()->subDays(30))
                ->exists();

            if (! $hasEvidence) {
                $missingEvidence[] = [
                    'assignment_id' => $assignment->id,
                    'worker_id' => $workerId,
                    'worker_name' => optional($assignment->worker)->first_name.' '.optional($assignment->worker)->last_name,
                    'start_date' => $assignment->start_date,
                ];
            }
        }

        return view('portal.participant.care_notes', compact('participant', 'notes', 'missingEvidence'));
    }

    public function storeChecklist(Request $request)
    {
        $user = auth()->user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'string', 'max:500'],
        ]);

        $title = 'Monthly Care Management Checklist - '.now()->format('F Y');

        Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => 'monthly_care_management_checklist',
            'title' => $title,
            'storage_disk' => 'local',
            'path' => '',
            'mime_type' => '',
            'size_bytes' => 0,
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'metadata' => ['items' => $validated['items'], 'submitted_at' => now()->toDateTimeString()],
            'is_sensitive' => false,
        ]);

        return redirect()->route('portal.participant.care_notes.index')->with('status', 'Monthly checklist saved.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'tasks_completed' => ['required', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'service_type' => ['nullable', 'string', 'max:100'],
            'risks_flag' => ['sometimes', 'boolean'],
            'service_confirmed' => ['accepted'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls', 'max:10240'],
        ]);

        $user = auth()->user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();
        $workerId = $user->worker?->id ?? $participant->assignments()->where('status', 'active')->latest()->value('worker_id');

        if (! $workerId) {
            return redirect()->back()->withErrors(['worker' => 'No worker assigned to this participant. Care notes require a worker.']);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('care_notes', 'local');
        }

        $careNote = CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $workerId,
            'shift_date' => $validated['shift_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'tasks_completed' => $validated['tasks_completed'],
            'care_summary' => $validated['tasks_completed'],
            'observations' => $validated['observations'] ?? null,
            'risks_flag' => $request->boolean('risks_flag'),
            'service_confirmed' => true,
            'attachment_path' => $attachmentPath,
            'service_type' => $request->input('service_type'),
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_id' => $user->id,
        ]);

        // Notify admins
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new CareNoteSubmitted($careNote));
            }
        } catch (\Exception $e) {
            // swallow notification errors to not block user flow
        }

        return redirect()->route('portal.participant.care_notes.index')->with('status', 'Care note saved.');
    }
}
