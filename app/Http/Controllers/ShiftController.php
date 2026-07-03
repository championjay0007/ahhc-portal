<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Shift;
use App\Models\Worker;
use App\Services\AuditLogService;
use App\Services\NotificationService;
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

        $shift = Shift::create($validated);
        AuditLogService::record('Shift Created', $shift, [], $shift->toArray());

        $this->sendAssignmentNotifications($shift, 'assigned');

        return Redirect::route('portal.admin.shifts.index')->with('status', 'Shift created and assigned successfully.');
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
        $validated = $this->validateShift($request, $shift);
        $oldValues = $shift->getOriginal();

        $shift->update($validated);
        AuditLogService::record('Shift Updated', $shift, $oldValues, $shift->getChanges());

        if ($shift->wasChanged('worker_id')) {
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
        return $this->cancel($shift);
    }

    protected function validateShift(Request $request, ?Shift $shift = null): array
    {
        return $request->validate([
            'participant_id' => ['required', Rule::exists('participants', 'id')],
            'worker_id' => ['nullable', Rule::exists('workers', 'id')],
            'service_type' => ['nullable', 'string', 'max:150'],
            'service_category' => ['nullable', 'string', 'max:150'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Shift::statuses())],
        ]);
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
