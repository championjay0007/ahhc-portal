<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Shift;
use App\Models\Worker;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Shift::with(['participant', 'worker']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->input('worker_id'));
        }

        if ($request->filled('participant_id')) {
            $query->where('participant_id', $request->input('participant_id'));
        }

        $shifts = $query->orderBy('shift_date', 'desc')->orderBy('start_time')->paginate(20)->withQueryString();
        $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $statuses = Shift::statuses();

        return view('portal.admin.shifts.index', compact('shifts', 'participants', 'workers', 'statuses'));
    }

    public function create()
    {
        $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $statuses = Shift::statuses();

        return view('portal.admin.shifts.create', compact('participants', 'workers', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateShift($request);

        // Only allow creating scheduled or confirmed shifts
        if (!in_array($validated['status'], [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED])) {
            $validated['status'] = Shift::STATUS_SCHEDULED;
        }

        $shift = Shift::create($validated);
        AuditLogService::record('Shift Created', $shift, [], $shift->toArray());

        if ($shift->worker_id) {
            $this->sendAssignmentNotifications($shift, 'assigned');
        }

        return Redirect::route('portal.admin.shifts.index')->with('status', 'Shift created successfully.');
    }

    public function edit(Shift $shift)
    {
        $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $statuses = Shift::statuses();

        return view('portal.admin.shifts.edit', compact('shift', 'participants', 'workers', 'statuses'));
    }

    public function update(Request $request, Shift $shift)
    {
        // Cannot update completed, cancelled, or missed shifts
        if (in_array($shift->status, [Shift::STATUS_COMPLETED, Shift::STATUS_CANCELLED, Shift::STATUS_MISSED])) {
            return Redirect::route('portal.admin.shifts.index')
                ->with('error', 'Cannot update '.strtolower(str_replace('_', ' ', $shift->status)).' shifts.');
        }

        $validated = $this->validateShift($request, $shift);
        $oldValues = $shift->getOriginal();

        $shift->update($validated);
        AuditLogService::record('Shift Updated', $shift, $oldValues, $shift->getChanges());

        // Clear reminder if shift was rescheduled
        if ($shift->wasChanged('shift_date') || $shift->wasChanged('start_time') || $shift->wasChanged('end_time')) {
            $shift->update(['reminder_sent_at' => null]);
        }

        if ($shift->wasChanged('worker_id') && $shift->worker_id) {
            $this->sendAssignmentNotifications($shift, 'reassigned');
        }

        return Redirect::route('portal.admin.shifts.index')->with('status', 'Shift updated successfully.');
    }

    public function cancel(Shift $shift)
    {
        $oldValues = $shift->getOriginal();
        $shift->update(['status' => Shift::STATUS_CANCELLED]);

        AuditLogService::record('Shift Cancelled', $shift, $oldValues, ['status' => Shift::STATUS_CANCELLED]);
        $this->sendCancellationNotifications($shift);

        return Redirect::route('portal.admin.shifts.index')->with('status', 'Shift cancelled successfully.');
    }

    public function destroy(Shift $shift)
    {
        $oldValues = $shift->toArray();
        
        // Detach related records instead of deleting them
        $shift->careNotes()->update(['shift_id' => null]);
        $shift->invoices()->update(['shift_id' => null]);
        $shift->incidents()->update(['shift_id' => null]);
        
        $shift->delete();
        AuditLogService::record('Shift Deleted', Shift::class, $oldValues, []);
        
        return Redirect::route('portal.admin.shifts.index')->with('status', 'Shift deleted successfully.');
    }

    protected function validateShift(Request $request, ?Shift $shift = null): array
    {
        $validated = $request->validate([
            'participant_id' => ['required', Rule::exists('participants', 'id')],
            'worker_id' => ['nullable', Rule::exists('workers', 'id')],
            'service_type' => ['nullable', 'string', 'max:150'],
            'service_category' => ['nullable', 'string', 'max:150'],
            'shift_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Shift::statuses())],
        ]);

        // Validate shift_date is not in the past
        $shiftDate = Carbon::createFromFormat('Y-m-d', $validated['shift_date'])->startOfDay();
        if ($shiftDate->isPast()) {
            $request->validate(['shift_date' => 'bail|after_or_equal:today'], 
                ['shift_date.after_or_equal' => 'Shift date must be today or in the future.']);
        }

        // Validate end_time is after start_time, accounting for overnight shifts
        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = Carbon::createFromFormat('H:i', $validated['end_time']);
        
        $endTimeCheck = clone $endTime;
        if ($endTimeCheck->lte($startTime)) {
            $endTimeCheck->addDay();
        }
        
        if ($endTimeCheck->lte($startTime)) {
            $request->validate(['end_time' => 'bail|after:start_time'], 
                ['end_time.after' => 'End time must be after start time.']);
        }

        // Validate shift duration (min 15 min, max 12 hours)
        $durationMinutes = $startTime->diffInMinutes($endTimeCheck);
        if ($durationMinutes < 15) {
            $request->validate(['end_time' => 'bail|after:start_time'], 
                ['end_time.after' => 'Shift must be at least 15 minutes long.']);
        }
        if ($durationMinutes > 720) {
            $request->validate(['end_time' => 'bail|after:start_time'], 
                ['end_time.after' => 'Shift cannot exceed 12 hours.']);
        }

        // Validate worker assignment if provided
        if ($validated['worker_id'] ?? null) {
            $this->validateWorkerAssignment($request, $validated, $shift);
        }

        return $validated;
    }

    protected function validateWorkerAssignment(Request $request, array &$validated, ?Shift $shift = null): void
    {
        $worker = Worker::find($validated['worker_id']);
        if (!$worker) {
            return;
        }

        $shiftDate = $validated['shift_date'];
        $shiftId = $shift?->id;

        // Check worker status - must be active
        if ($worker->status !== 'approved') {
            $request->validate(['worker_id' => 'bail|exists:workers,id'], 
                ['worker_id.exists' => "This worker is not currently approved for shifts."]);
        }

        // Check worker compliance - must not be expired
        if ($worker->compliance_expiry_at && $worker->compliance_expiry_at->isPast()) {
            $request->validate(['worker_id' => 'bail|exists:workers,id'], 
                ['worker_id.exists' => "This worker's compliance has expired."]);
        }

        // Check background check - must not be expired
        if ($worker->background_check_expiry_at && $worker->background_check_expiry_at->isPast()) {
            $request->validate(['worker_id' => 'bail|exists:workers,id'], 
                ['worker_id.exists' => "This worker's background check has expired."]);
        }

        // Check for overlapping shifts
        $overlappingShift = Shift::where('worker_id', $worker->id)
            ->where('shift_date', $shiftDate)
            ->when($shiftId, fn($q) => $q->where('id', '!=', $shiftId))
            ->whereIn('status', [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED, Shift::STATUS_IN_PROGRESS])
            ->first();

        if ($overlappingShift) {
            $newStart = Carbon::createFromFormat('H:i', $validated['start_time']);
            $newEnd = Carbon::createFromFormat('H:i', $validated['end_time']);
            $existingStart = Carbon::createFromFormat('H:i', $overlappingShift->start_time);
            $existingEnd = Carbon::createFromFormat('H:i', $overlappingShift->end_time);

            // Handle overnight shifts
            if ($newEnd->lte($newStart)) {
                $newEnd->addDay();
            }
            if ($existingEnd->lte($existingStart)) {
                $existingEnd->addDay();
            }

            if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                $request->validate(['worker_id' => 'bail|exists:workers,id'], 
                    ['worker_id.exists' => "Worker already has a shift on this date at {$overlappingShift->start_time}-{$overlappingShift->end_time}."]);
            }
        }
    }

    protected function sendAssignmentNotifications(Shift $shift, string $type): void
    {
        $worker = $shift->worker;
        $participant = $shift->participant;
        $title = $type === 'reassigned' ? 'Shift re-assigned' : 'Shift assigned';
        $message = sprintf(
            'A shift for %s on %s has been %s. %s to %s.',
            $participant->first_name,
            $shift->shift_date->format('d M Y'),
            $type === 'reassigned' ? 'reassigned' : 'assigned',
            $shift->service_type ? $shift->service_type.' -' : '',
            $shift->location ?? 'location not set'
        );

        if ($worker && $worker->user_id) {
            NotificationService::notify([
                'user_id' => $worker->user_id,
                'type' => 'info',
                'title' => $title,
                'message' => $message,
                'data' => [
                    'url' => route('portal.worker.shifts'),
                    'shift_id' => $shift->id,
                ],
            ]);
        }

        if ($participant && $participant->user_id) {
            NotificationService::notify([
                'user_id' => $participant->user_id,
                'type' => 'info',
                'title' => ucfirst($type).' shift scheduled',
                'message' => $message,
                'data' => [
                    'url' => route('portal.participant.services'),
                    'shift_id' => $shift->id,
                ],
            ]);
        }
    }

    protected function sendCancellationNotifications(Shift $shift): void
    {
        $worker = $shift->worker;
        $participant = $shift->participant;
        $message = sprintf(
            'The shift for %s on %s has been cancelled.',
            $participant->first_name,
            $shift->shift_date->format('d M Y')
        );

        if ($worker && $worker->user_id) {
            NotificationService::notify([
                'user_id' => $worker->user_id,
                'type' => 'warning',
                'title' => 'Shift cancelled',
                'message' => $message,
                'data' => [
                    'url' => route('portal.worker.shifts'),
                    'shift_id' => $shift->id,
                ],
            ]);
        }

        if ($participant && $participant->user_id) {
            NotificationService::notify([
                'user_id' => $participant->user_id,
                'type' => 'warning',
                'title' => 'Shift cancelled',
                'message' => $message,
                'data' => [
                    'url' => route('portal.participant.services'),
                    'shift_id' => $shift->id,
                ],
            ]);
        }
    }
}
