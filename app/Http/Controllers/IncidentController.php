<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Participant;
use App\Models\User;
use App\Notifications\IncidentReported;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class IncidentController extends Controller
{
    public function indexForParticipant(Participant $participant)
    {
        $incidents = $participant->incidents()->latest()->get();

        return view('portal.participant.incidents', compact('participant', 'incidents'));
    }

    public function storeForParticipant(Participant $participant, Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:incident,hazard,near_miss,complaint'],
            'description' => ['required', 'string', 'max:2000'],
            'severity' => ['required', 'string', 'in:low,medium,high'],
        ]);

        $incident = Incident::create([
            'participant_id' => $participant->id,
            'worker_id' => auth()->user()->worker?->id,
            'reported_by_id' => auth()->id(),
            'incident_type' => $validated['type'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'status' => 'open',
        ]);

        AuditLogService::record('Incident Create', $incident, [], [
            'participant_id' => $incident->participant_id,
            'incident_type' => $incident->incident_type,
            'severity' => $incident->severity,
        ]);

        // notify admins
        try {
            $admins = User::where('role', 'admin')->get()->filter(fn ($user) => is_string($user->email ?? null) && filter_var($user->email, FILTER_VALIDATE_EMAIL));
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new IncidentReported($incident));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->back()->with('status', 'Incident recorded.');
    }
}
