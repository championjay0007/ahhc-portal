<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ParticipantOnboardingInvitation;
use App\Models\Agreement;
use App\Models\Enquiry;
use App\Models\Participant;
use App\Models\User;
use App\Services\NotificationCenterService;
use App\Services\TemplateMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Enquiry::with('assignedTo')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        $enquiries = $query->paginate(20)->withQueryString();
        $admins = User::whereIn('role', ['admin', 'system_admin'])->orderBy('name')->get();
        $statuses = Enquiry::statusOptions();

        return view('admin.enquiries.index', compact('enquiries', 'admins', 'statuses'));
    }

    public function show(Enquiry $enquiry)
    {
        $admins = User::whereIn('role', ['admin', 'system_admin'])->orderBy('name')->get();
        $statuses = Enquiry::statusOptions();

        return view('admin.enquiries.show', compact('enquiry', 'admins', 'statuses'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Enquiry::statusOptions())],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where(function ($query) {
                $query->whereIn('role', ['admin', 'system_admin']);
            })],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $enquiry->update($validated);

        return back()->with('status', 'Enquiry status updated successfully.');
    }

    public function inviteParticipant(Enquiry $enquiry)
    {
        if ($enquiry->role !== 'participant') {
            return back()->withErrors('This invitation flow is only available for participant enquiries.');
        }

        if (User::where('email', $enquiry->email)->exists()) {
            return back()->withErrors('A user with this email already exists. Please manage the account directly.');
        }

        $parts = preg_split('/\s+/', trim($enquiry->name));
        $firstName = $parts[0] ?? $enquiry->name;
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        $user = User::create([
            'name' => $enquiry->name,
            'email' => $enquiry->email,
            'phone' => $enquiry->phone,
            'role' => 'participant',
            'status' => 'inactive',
            'mfa_enabled' => false,
            'password' => Hash::make(Str::random(32)),
            'password_changed_at' => null,
        ]);

        $participant = Participant::where('email', $enquiry->email)->first();

        if ($participant) {
            $participant->fill([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'preferred_name' => $firstName,
                'status' => Participant::STATUS_ONBOARDING,
                'onboarding_status' => 'invitation_sent',
                'onboarding_token' => $participant->onboarding_token ?? Str::uuid(),
                'onboarding_expires_at' => $participant->onboarding_expires_at ?? now()->addDays(14),
                'email' => $enquiry->email,
                'phone' => $enquiry->phone,
                'updated_by_id' => auth()->id(),
            ])->save();
        } else {
            $participant = Participant::create([
                'user_id' => $user->id,
                'participant_number' => 'P-'.$user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'preferred_name' => $firstName,
                'status' => Participant::STATUS_ONBOARDING,
                'onboarding_status' => 'invitation_sent',
                'onboarding_token' => Str::uuid(),
                'onboarding_expires_at' => now()->addDays(14),
                'email' => $enquiry->email,
                'phone' => $enquiry->phone,
                'created_by_id' => auth()->id(),
                'updated_by_id' => auth()->id(),
            ]);
        }

        $requiredAgreementIds = Agreement::where('is_required', true)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (! empty($requiredAgreementIds)) {
            $participant->agreements()->syncWithoutDetaching($requiredAgreementIds);
        }

        $html = view('mail.participant-onboarding-invitation', ['participant' => $participant])->render();
        try {
            TemplateMailer::send(
                $user->email,
                'participant-onboarding-invitation',
                [
                    'name' => trim($participant->first_name.' '.$participant->last_name),
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'email' => $participant->email,
                    'onboarding_url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                    'expires_at' => optional($participant->onboarding_expires_at)->format('d M Y H:i') ?? now()->addDays(30)->format('d M Y H:i'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'Complete your AHHC portal onboarding',
                $html,
                strip_tags($html),
                'Participant Onboarding Invitation',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            Mail::to($user->email)->send(new ParticipantOnboardingInvitation($participant));
        }

        NotificationCenterService::send('portal_invitation', $user->id, [
            'participant_id' => $participant->id,
            'url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
            'message' => 'Your onboarding invitation has been sent. Use the link to complete your onboarding.',
        ]);

        $enquiry->update([
            'status' => Enquiry::STATUS_APPROVED,
            'notes' => trim(($enquiry->notes ?? '')."\nInvitation sent to {$enquiry->email} on ".now()->format('Y-m-d H:i')),
        ]);

        return back()->with('status', 'Onboarding invitation sent successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('portal.admin.enquiries.index')->with('status', 'Enquiry deleted successfully.');
    }

    public function export()
    {
        $fileName = 'ahhc-enquiries-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Role',
                'Support At Home Status',
                'Consent',
                'Status',
                'Assigned To',
                'Notes',
                'Message',
                'Created At',
                'Updated At',
            ]);

            Enquiry::with('assignedTo')->orderByDesc('created_at')->chunk(200, function ($enquiries) use ($handle) {
                foreach ($enquiries as $enquiry) {
                    fputcsv($handle, [
                        $enquiry->id,
                        $enquiry->name,
                        $enquiry->email,
                        $enquiry->phone,
                        $enquiry->role,
                        $enquiry->support_at_home_status,
                        $enquiry->consent ? 'Yes' : 'No',
                        $enquiry->status,
                        $enquiry->assignedTo?->name,
                        Str::limit($enquiry->notes, 100),
                        Str::limit($enquiry->message, 300),
                        $enquiry->created_at->toDateTimeString(),
                        $enquiry->updated_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
