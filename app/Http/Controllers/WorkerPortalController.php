<?php

namespace App\Http\Controllers;

use App\Models\CareNote;
use App\Models\Document;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\ParticipantAssignment;
use App\Models\Shift;
use App\Models\User;
use App\Models\Worker;
use App\Notifications\CareNoteSubmitted;
use App\Notifications\IncidentReported;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkerPortalController extends Controller
{
    protected function worker()
    {
        $user = Auth::user();

        if ($user->role !== 'worker') {
            abort(403, 'Unauthorized.');
        }

        $worker = $user->worker;

        if (! $worker) {
            [$firstName, $lastName] = $this->splitFullName($user->name);

            $worker = Worker::create([
                'user_id' => $user->id,
                'worker_number' => 'W-'.$user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $user->phone,
                'email' => $user->email,
                'role_type' => 'worker',
                'status' => 'active',
            ]);
        }

        return $worker;
    }

    protected function splitFullName(string $fullName): array
    {
        $trimmedName = trim($fullName);

        if ($trimmedName === '') {
            return ['User', 'User'];
        }

        $parts = preg_split('/\s+/', $trimmedName);
        $firstName = $parts[0] ?? 'User';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'User';

        return [$firstName, $lastName];
    }

    public function dashboard()
    {
        $worker = $this->worker();
        $assignments = $worker->assignments()->with('participant')->where('status', 'active')->get();
        $today = now()->startOfDay();

        $todaysShifts = $assignments->filter(function ($assignment) use ($today) {
            return $assignment->start_date && $assignment->start_date->isSameDay($today);
        });

        $notesDueCount = CareNote::where('worker_id', $worker->id)
            ->whereNull('submitted_at')
            ->count();

        $incidentsOpen = Incident::where('worker_id', $worker->id)
            ->where('status', 'open')
            ->count();

        $complianceDays = null;
        if ($worker->compliance_expiry_at) {
            $complianceDays = now()->diffInDays($worker->compliance_expiry_at, false);
        }

        return view('portal.worker.dashboard', [
            'worker' => $worker,
            'assignments' => $assignments,
            'todaysShifts' => $todaysShifts,
            'notesDueCount' => $notesDueCount,
            'incidentsOpen' => $incidentsOpen,
            'complianceDays' => $complianceDays,
        ]);
    }

    public function assignedParticipants()
    {
        $worker = $this->worker();
        $assignments = $worker->assignments()
            ->with(['participant.supportPerson'])
            ->where('status', 'active')
            ->get();

        return view('portal.worker.assigned-participants', compact('worker', 'assignments'));
    }

    public function showParticipant(Participant $participant)
    {
        $worker = $this->worker();

        $assignment = ParticipantAssignment::where('worker_id', $worker->id)
            ->where('participant_id', $participant->id)
            ->where('status', 'active')
            ->first();

        abort_unless($assignment, 403, 'Participant not assigned to you.');

        // Only expose limited participant data to workers/suppliers.
        // Workers should only see the participant's name, address, phone, and the shifts assigned to them.
        $shifts = $worker->assignments()
            ->where('participant_id', $participant->id)
            ->where('status', 'active')
            ->orderBy('start_date', 'asc')
            ->get(['id', 'start_date', 'end_date', 'assignment_type']);

        return view('portal.worker.participant', compact('worker', 'participant', 'assignment', 'shifts'));
    }

    public function shifts()
    {
        $worker = $this->worker();
        $shifts = Shift::with('participant')
            ->where('worker_id', $worker->id)
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('portal.worker.shifts', compact('worker', 'shifts'));
    }

    public function confirmShift(Shift $shift)
    {
        $worker = $this->worker();
        
        // Check if worker is assigned to this shift
        if ($shift->worker_id !== $worker->id) {
            abort(403, 'You are not assigned to this shift.');
        }

        // Check if shift is in correct status
        if ($shift->status !== Shift::STATUS_SCHEDULED) {
            abort(422, 'Shift can only be confirmed from scheduled status.');
        }

        $oldValues = $shift->getOriginal();
        $shift->update(['status' => Shift::STATUS_CONFIRMED]);
        AuditLogService::record('Shift Confirmed', $shift, $oldValues, $shift->getChanges());

        return redirect()->route('portal.worker.shifts')->with('status', 'Shift confirmed successfully.');
    }

    public function startShift(Shift $shift)
    {
        $worker = $this->worker();
        
        // Check if worker is assigned to this shift
        if ($shift->worker_id !== $worker->id) {
            abort(403, 'You are not assigned to this shift.');
        }

        // Check if shift is in correct status
        if (!in_array($shift->status, [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED], true)) {
            abort(422, 'Shift can only be started from scheduled or confirmed status.');
        }

        $oldValues = $shift->getOriginal();
        $shift->update([
            'status' => Shift::STATUS_IN_PROGRESS,
            'started_at' => $shift->started_at ?? now(),
        ]);
        AuditLogService::record('Shift Started', $shift, $oldValues, $shift->getChanges());

        return redirect()->route('portal.worker.shifts')->with('status', 'Shift started.');
    }

    public function completeShift(Shift $shift)
    {
        $worker = $this->worker();
        
        // Check if worker is assigned to this shift
        if ($shift->worker_id !== $worker->id) {
            abort(403, 'You are not assigned to this shift.');
        }

        // Check if shift is in correct status
        if ($shift->status !== Shift::STATUS_IN_PROGRESS) {
            abort(422, 'Shift can only be completed from in-progress status.');
        }

        $oldValues = $shift->getOriginal();
        $shift->update([
            'status' => Shift::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
        AuditLogService::record('Shift Completed', $shift, $oldValues, $shift->getChanges());

        return redirect()->route('portal.worker.shifts')->with('status', 'Shift completed successfully.');
    }

    public function createCareNote()
    {
        $worker = $this->worker();
        $assignments = $worker->assignments()->with('participant')->where('status', 'active')->get();
        $shifts = Shift::with('participant')
            ->where('worker_id', $worker->id)
            ->whereDate('shift_date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('shift_date', 'desc')
            ->get();

        return view('portal.worker.care-note', compact('worker', 'assignments', 'shifts'));
    }

    public function storeCareNote(Request $request)
    {
        $worker = $this->worker();
        $participantIds = $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray();

        $validated = $request->validate([
            'participant_id' => ['required', Rule::in($participantIds)],
            'shift_id' => ['nullable', Rule::exists('shifts', 'id')],
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

        if (! empty($validated['shift_id'])) {
            $shift = Shift::findOrFail($validated['shift_id']);
            abort_unless($shift->worker_id === $worker->id, 403, 'Invalid shift selection.');
        }

        $participant = Participant::findOrFail($validated['participant_id']);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('care_notes', 'local');
        }

        $careNote = CareNote::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'shift_id' => $validated['shift_id'] ?? null,
            'shift_date' => $validated['shift_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'tasks_completed' => $validated['tasks_completed'],
            'care_summary' => $validated['tasks_completed'],
            'observations' => $validated['observations'] ?? null,
            'risks_flag' => $request->boolean('risks_flag'),
            'service_confirmed' => true,
            'attachment_path' => $attachmentPath,
            'service_type' => $validated['service_type'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_id' => Auth::id(),
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

        return redirect()->route('portal.worker.care_notes.create')->with('status', 'Care note submitted successfully.');
    }

    public function createIncident()
    {
        $worker = $this->worker();
        $assignments = $worker->assignments()->with('participant')->where('status', 'active')->get();
        $shifts = Shift::with('participant')
            ->where('worker_id', $worker->id)
            ->whereDate('shift_date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('shift_date', 'desc')
            ->get();

        return view('portal.worker.incident', compact('worker', 'assignments', 'shifts'));
    }

    public function storeIncident(Request $request)
    {
        $worker = $this->worker();
        $participantIds = $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray();

        $validated = $request->validate([
            'participant_id' => ['required', Rule::in($participantIds)],
            'shift_id' => ['nullable', Rule::exists('shifts', 'id')],
            'type' => ['required', 'string', 'in:incident,hazard,near_miss,complaint'],
            'description' => ['required', 'string', 'max:2000'],
            'severity' => ['required', 'string', 'in:low,medium,high'],
        ]);

        if (! empty($validated['shift_id'])) {
            $shift = Shift::findOrFail($validated['shift_id']);
            abort_unless($shift->worker_id === $worker->id, 403, 'Invalid shift selection.');
        }

        $incident = Incident::create([
            'participant_id' => $validated['participant_id'],
            'worker_id' => $worker->id,
            'shift_id' => $validated['shift_id'] ?? null,
            'reported_by_id' => Auth::id(),
            'incident_type' => $validated['type'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'status' => 'open',
        ]);

        // Notify admins
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new IncidentReported($incident));
            }
        } catch (\Exception $e) {
            // swallow notification errors to not block user flow
        }

        return redirect()->route('portal.worker.incidents.create')->with('status', 'Incident report submitted.');
    }

    public function uploadDocuments()
    {
        $worker = $this->worker();
        $assignments = $worker->assignments()->with('participant')->where('status', 'active')->get();

        return view('portal.worker.documents', compact('worker', 'assignments'));
    }

    public function storeDocument(Request $request)
    {
        $worker = $this->worker();
        $participantIds = $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray();

        $validated = $request->validate([
            'participant_id' => ['required', Rule::in($participantIds)],
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:100'],
            'file' => Document::fileValidationRules(),
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $validated['participant_id'],
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => Auth::id(),
            'status' => 'uploaded',
            'is_sensitive' => true,
        ]);

        $document->versions()->create([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => Auth::id(),
            'version_number' => 1,
        ]);

        return redirect()->route('portal.worker.documents.upload')->with('status', 'Document uploaded successfully.');
    }

    public function invoices()
    {
        $worker = $this->worker();
        $participantIds = $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray();

        $invoices = Invoice::query()
            ->with('participant')
            ->where('worker_id', $worker->id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        $assignments = $worker->assignments()->with('participant')->where('status', 'active')->get();

        return view('portal.worker.invoices', compact('worker', 'assignments', 'invoices'));
    }

    public function storeInvoice(Request $request)
    {
        $worker = $this->worker();
        $participantIds = $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray();

        $validated = $request->validate([
            'participant_id' => ['required', Rule::in($participantIds)],
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'service_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls', 'max:10240'],
        ]);

        $amountCents = (int) round($validated['amount'] * 100);
        unset($validated['amount']);

        $file = $request->file('attachment');
        $path = $file->store('invoices', 'local');
        $attachmentDisk = 'local';
        $attachmentMimeType = $file->getMimeType();

        try {
            $invoice = Invoice::create([
            'participant_id' => $validated['participant_id'],
            'worker_id' => $worker->id,
            'invoice_number' => $validated['invoice_number'],
            'status' => 'submitted',
            'amount_cents' => $amountCents,
            'invoice_date' => $validated['invoice_date'],
            'service_date' => $validated['service_date'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'invoice_file_path' => $path,
            'attachment_path' => $path,
            'attachment_disk' => $attachmentDisk,
            'attachment_mime_type' => $attachmentMimeType,
        ]);
        } catch (QueryException $e) {
            // Unique invoice number conflict
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'UNIQUE')) {
                if ($path && Storage::disk($attachmentDisk)->exists($path)) {
                    Storage::disk($attachmentDisk)->delete($path);
                }

                return back()->withErrors(['invoice_number' => 'An invoice with that number already exists. Please choose a different invoice number.'])->withInput();
            }

            throw $e;
        }

        AuditLogService::record('Worker Invoice Submitted', $invoice, [], [
            'invoice_number' => $invoice->invoice_number,
            'participant_id' => $invoice->participant_id,
            'worker_id' => $invoice->worker_id,
            'amount_cents' => $invoice->amount_cents,
        ]);

        return redirect()->route('portal.worker.invoices')->with('status', 'Invoice submitted successfully.');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $worker = $this->worker();

        abort_unless($invoice->worker_id === $worker->id, 403, 'Unauthorized.');

        $path = $invoice->invoice_file_path ?: $invoice->attachment_path;
        $disk = $invoice->attachment_disk ?: 'local';

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $filename = $invoice->invoice_number.'.'.pathinfo($path, PATHINFO_EXTENSION);
        AuditLogService::record('Worker Invoice Download', $invoice, [], []);

        return Storage::disk($disk)->download($path, $filename);
    }

    public function forms()
    {
        $worker = $this->worker();

        $documents = Document::query()
            ->where('owner_type', Worker::class)
            ->where('owner_id', $worker->id)
            ->where('status', 'uploaded')
            ->latest()
            ->get();

        return view('portal.worker.forms', compact('worker', 'documents'));
    }

    public function showForm(Document $document)
    {
        $worker = $this->worker();

        $document = Document::query()
            ->with('signatures.signedBy')
            ->where('id', $document->id)
            ->where('owner_type', Worker::class)
            ->where('owner_id', $worker->id)
            ->firstOrFail();

        return view('portal.worker.form', compact('document'));
    }

    public function signForm(Request $request, Document $document)
    {
        $request->validate([
            'confirm_signature' => ['accepted'],
            'signature_image' => ['nullable', 'string'],
        ]);

        $worker = $this->worker();

        $document = Document::query()
            ->with('signatures')
            ->where('id', $document->id)
            ->where('owner_type', Worker::class)
            ->where('owner_id', $worker->id)
            ->firstOrFail();

        if ($document->signatures()->exists()) {
            return back()->with('status', 'This document has already been signed.');
        }

        $signatureData = [
            'signed_by_type' => get_class($worker->user),
            'signed_by_id' => $worker->user->id,
            'signature_method' => 'electronic',
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signature_hash' => hash('sha256', $document->id.$worker->user->id.$request->ip().now()->timestamp),
        ];

        if ($request->filled('signature_image')) {
            $data = $request->input('signature_image');
            if (strpos($data, 'base64,') !== false) {
                [$meta, $content] = explode(',', $data, 2);
                $decoded = base64_decode($content);
                if ($decoded !== false) {
                    $filename = 'signatures/'.$document->id.'_'.time().'_'.uniqid().'.png';
                    Storage::disk('local')->put($filename, $decoded);
                    $signatureData['signature_path'] = $filename;
                    $signatureData['signature_disk'] = 'local';
                }
            }
        }

        $document->signatures()->create($signatureData);
        $document->update(['status' => 'signed']);

        return redirect()->route('portal.worker.forms')->with('status', 'Document signed successfully.');
    }

    public function downloadForm(Document $document)
    {
        $worker = $this->worker();

        $document = Document::query()
            ->where('id', $document->id)
            ->where('owner_type', Worker::class)
            ->where('owner_id', $worker->id)
            ->firstOrFail();

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    public function profile()
    {
        $worker = $this->worker();
        $activeAssignments = $worker->assignments()->where('status', 'active')->count();

        $complianceDays = null;
        if ($worker->compliance_expiry_at) {
            $complianceDays = now()->diffInDays($worker->compliance_expiry_at, false);
        }

        return view('portal.worker.profile', compact('worker', 'activeAssignments', 'complianceDays'));
    }

    public function updateProfile(Request $request)
    {
        $worker = $this->worker();
        $user = $worker->user;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $worker->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('profile_photo')->storePublicly('profile_photos', 'public');
        }

        $user->update([
            'name' => $validated['first_name'].' '.$validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'profile_photo_path' => $validated['profile_photo_path'] ?? $user->profile_photo_path,
        ]);

        return back()->with('status', 'Profile updated successfully.');
    }
}
