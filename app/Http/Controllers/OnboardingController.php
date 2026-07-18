<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Document;
use App\Models\OnboardingSubmission;
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show onboarding form for token holder
     */
    public function show(string $token): View
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('onboarding_expires_at', '>=', now())
            ->firstOrFail();

        if ($participant->onboarding_status !== 'invitation_sent' && $participant->onboarding_status !== 'changes_requested') {
            abort(403, 'This onboarding link is no longer valid.');
        }

        $agreements = $participant->agreements()->where('is_active', true)->get();

        return view('participant.onboarding.form', compact('participant', 'token', 'agreements'));
    }

    /**
     * Submit onboarding form
     */
    public function submit(string $token, Request $request)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('onboarding_expires_at', '>=', now())
            ->firstOrFail();

        // Validate all required sections
        $validated = $request->validate([
            // Account Setup
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Personal Information
            'full_name' => ['required', 'string', 'max:150'],
            'preferred_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:50'],
            'postcode' => ['required', 'string', 'max:20'],
            'emergency_contact_name' => ['required', 'string', 'max:150'],
            'emergency_contact_phone' => ['required', 'string', 'max:50'],
            'emergency_contact_relationship' => ['required', 'string', 'max:100'],

            // Support Person (optional)
            'support_person_first_name' => ['nullable', 'string', 'max:100'],
            'support_person_last_name' => ['nullable', 'string', 'max:100'],
            'support_person_email' => ['nullable', 'email', 'max:150'],
            'support_person_phone' => ['nullable', 'string', 'max:50'],
            'support_person_relationship' => ['nullable', 'string', 'max:100'],

            // Documents
            'documents.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],

            // Agreements
            'agreement_signatures.*' => ['required', 'string'], // Base64 signatures
        ]);

        // Update participant with personal data
        $participant->update([
            'preferred_name' => $validated['preferred_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postcode' => $validated['postcode'],
        ]);

        // Update user password
        if (Auth::check() && Auth::user()->id === $participant->user_id) {
            $participant->user->update([
                'password' => bcrypt($validated['password']),
            ]);
        }

        // Handle document uploads
        $uploadedDocuments = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('onboarding-documents', 'private');
                $uploadedDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Store support person data
        $supportPersonData = null;
        if ($validated['support_person_first_name']) {
            $supportPersonData = [
                'first_name' => $validated['support_person_first_name'],
                'last_name' => $validated['support_person_last_name'],
                'email' => $validated['support_person_email'],
                'phone' => $validated['support_person_phone'],
                'relationship' => $validated['support_person_relationship'],
            ];
        }

        // Create onboarding submission
        $submission = OnboardingSubmission::create([
            'participant_id' => $participant->id,
            'personal_data' => [
                'full_name' => $validated['full_name'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'],
            ],
            'support_person_data' => $supportPersonData,
            'uploaded_documents' => $uploadedDocuments,
            'signed_agreements' => $validated['agreement_signatures'] ?? null,
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        // Update participant status
        $participant->update([
            'onboarding_status' => 'pending_review',
            'onboarding_token' => null,
            'onboarding_expires_at' => null,
        ]);

        // TODO: Send notification to admin
        // TODO: Log audit trail

        return redirect()->route('participant.onboarding.status')
            ->with('success', 'Your onboarding has been submitted for review.');
    }

    /**
     * Show a single assigned agreement to the participant during onboarding.
     */
    public function showAgreement(string $token, Agreement $agreement): View
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('onboarding_expires_at', '>=', now())
            ->firstOrFail();

        if (! $participant->agreements()->where('agreements.id', $agreement->id)->where('agreements.is_active', true)->exists()) {
            abort(403, 'This agreement is not assigned to you.');
        }

        return view('participant.onboarding.agreement', compact('participant', 'agreement', 'token'));
    }

    /**
     * Download an assigned agreement during onboarding.
     */
    public function downloadAgreement(string $token, Agreement $agreement)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('onboarding_expires_at', '>=', now())
            ->firstOrFail();

        if (! $participant->agreements()->where('agreements.id', $agreement->id)->where('agreements.is_active', true)->exists()) {
            abort(403, 'This agreement is not assigned to you.');
        }

        $pdf = Pdf::loadView('pdfs.onboarding-agreement-preview', compact('agreement'));
        $fileName = Str::slug($agreement->title ?? 'agreement').'.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Show onboarding status page
     */
    public function status(): View
    {
        $participant = Auth::user()->participant;

        if (! $participant) {
            return redirect()->route('portal.login');
        }

        if ($participant->isActivated()) {
            return redirect()->route('portal.dashboard');
        }

        $submission = $participant->latestOnboardingSubmission();
        $unsignedAgreements = $participant->getUnsignedRequiredAgreements();

        $checklist = [
            'account_setup' => true, // If we got this far, password was set
            'personal_information' => $submission?->personal_data !== null,
            'documents_uploaded' => $submission?->uploaded_documents !== null,
            'agreements_signed' => $participant->hasSignedAllRequiredAgreements(),
            'submitted' => $submission !== null,
            'approved' => $participant->isOnboardingApproved(),
        ];

        return view('participant.onboarding.status', compact(
            'participant',
            'submission',
            'unsignedAgreements',
            'checklist'
        ));
    }

    /**
     * Sign agreement
     */
    public function signAgreement(Request $request)
    {
        $participant = Auth::user()->participant;
        if (! $participant) {
            abort(401);
        }

        $validated = $request->validate([
            'agreement_id' => ['required', 'exists:agreements,id'],
            'signature_image' => ['required', 'string'], // Base64
        ]);

        // Check if agreement is assigned to participant
        if (! $participant->agreements()->where('agreements.id', $validated['agreement_id'])->exists()) {
            abort(403, 'This agreement is not assigned to you.');
        }

        // Store signature
        $signature = $participant->signedAgreements()->updateOrCreate(
            ['agreement_id' => $validated['agreement_id']],
            [
                'signature_image' => $validated['signature_image'],
                'signed_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Agreement signed successfully.',
        ]);
    }
}
