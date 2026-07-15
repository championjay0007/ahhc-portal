<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentView;
use App\Models\OnboardingProgress;
use App\Models\Participant;
use App\Models\ParticipantDocumentSignature;
use App\Models\PortalSetting;
use App\Models\SupportPerson;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationCenterService;
use App\Services\NotificationService;
use App\Services\OnboardingAgreementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ParticipantOnboardingController extends Controller
{
    public function show(string $token)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            return redirect()->route('portal.login')
                ->withErrors(['token' => 'This onboarding link is invalid or has expired. Please contact AHHC support.']);
        }

        $progress = OnboardingProgress::firstOrCreate(
            ['participant_id' => $participant->id],
            [
                'current_step' => 1,
                'completed_steps' => [],
                'draft_data' => [],
                'status' => 'in_progress',
            ]
        );

        $requireMfa = $this->isMfaRequiredForOnboarding();
        $draftData = $progress->draft_data ?? [];
        $supportPerson = $participant->supportPerson;
        $agreements = $participant->agreements()->where('is_active', true)->get();

        // Admin-uploaded onboarding documents assigned to this participant (forms marked as required for onboarding)
        $adminAssignedDocs = Document::query()
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->where('onboarding_required', true)
            ->where('status', 'active')
            ->get();

        return view('auth.onboarding', compact('participant', 'token', 'requireMfa', 'progress', 'draftData', 'supportPerson', 'agreements', 'adminAssignedDocs'));
    }

    public function showAgreement(string $token, Agreement $agreement)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant || ! $participant->agreements()->where('agreements.id', $agreement->id)->where('agreements.is_active', true)->exists()) {
            abort(403, 'This agreement is not assigned to you.');
        }

        return view('participant.onboarding.agreement', [
            'participant' => $participant,
            'agreement' => $agreement,
            'token' => $token,
            'showRouteName' => 'portal.onboarding.agreement.show',
            'downloadRouteName' => 'portal.onboarding.agreement.download',
            'backRouteName' => 'portal.onboarding.show',
        ]);
    }

    public function downloadAgreement(string $token, Agreement $agreement)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant || ! $participant->agreements()->where('agreements.id', $agreement->id)->where('agreements.is_active', true)->exists()) {
            abort(403, 'This agreement is not assigned to you.');
        }

        $pdf = Pdf::loadView('pdfs.onboarding-agreement-preview', compact('agreement'));
        $fileName = Str::slug($agreement->title ?? 'agreement').'.pdf';

        return $pdf->download($fileName);
    }

    public function showOnboardingDocument(string $token, Document $document)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            return redirect()->route('portal.login')
                ->withErrors(['token' => 'This onboarding link is invalid or has expired. Please contact AHHC support.']);
        }

        if (! $document->onboarding_required || $document->status !== 'active') {
            abort(404);
        }

        return view('auth.onboarding.onboarding_document', compact('participant', 'token', 'document'));
    }

    public function previewOnboardingDocument(string $token, Document $document)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            abort(404);
        }

        if (! $document->onboarding_required || $document->status !== 'active') {
            abort(404);
        }

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return response()->file(Storage::disk($document->storage_disk)->path($document->path), [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.$document->title.'"',
        ]);
    }

    public function downloadOnboardingDocument(string $token, Document $document)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            abort(404);
        }

        if (! $document->onboarding_required || $document->status !== 'active') {
            abort(404);
        }

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    public function downloadSupportingDocument(string $token, string $id)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            abort(404);
        }

        // Find the document that contains this supporting id
        $documents = Document::whereNotNull('metadata')->get();
        $found = null;
        $entry = null;
        foreach ($documents as $doc) {
            $meta = $doc->metadata ?? [];
            $supporting = $meta['supporting_documents'] ?? [];
            foreach ($supporting as $s) {
                if (($s['id'] ?? null) === $id) {
                    $found = $doc;
                    $entry = $s;
                    break 2;
                }
            }
        }

        if (! $found || ! $entry) {
            abort(404);
        }

        // Ensure the document is part of the onboarding flow and active
        if (! $found->onboarding_required || $found->status !== 'active') {
            abort(404);
        }

        $path = $entry['path'] ?? null;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, $entry['name'] ?? basename($path));
    }

    public function markSupportingViewed(Request $request, string $token, string $id)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            return response()->json(['ok' => false], 404);
        }

        // locate parent document and ensure access
        $documents = Document::whereNotNull('metadata')->get();
        $found = null;
        foreach ($documents as $doc) {
            $meta = $doc->metadata ?? [];
            $supporting = $meta['supporting_documents'] ?? [];
            foreach ($supporting as $s) {
                if (($s['id'] ?? null) === $id) {
                    $found = $doc;
                    break 2;
                }
            }
        }

        if (! $found) {
            return response()->json(['ok' => false], 404);
        }

        // record view
        DocumentView::create([
            'participant_id' => $participant->id,
            'document_id' => $found->id,
            'supporting_id' => $id,
            'viewed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function supportingViewStatus(string $token)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            return response()->json(['ok' => false], 404);
        }

        $views = DocumentView::where('participant_id', $participant->id)
            ->pluck('supporting_id')
            ->unique()
            ->values()
            ->toArray();

        return response()->json(['ok' => true, 'viewed' => $views]);
    }

    public function signOnboardingDocument(Request $request, string $token, Document $document)
    {
        $request->validate([
            'confirm_signature' => ['accepted'],
            'signature_image' => ['required', 'string'],
        ]);

        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            return redirect()->route('portal.login')
                ->withErrors(['token' => 'This onboarding link is invalid or has expired. Please contact AHHC support.']);
        }

        if (! $document->onboarding_required || $document->status !== 'active') {
            return back()->with('status', 'This document is not available for onboarding signing.');
        }

        if ($document->signatures()->exists()) {
            return back()->with('status', 'This document has already been signed.');
        }

        // create signature record
        $signatureData = [
            'signed_by_type' => get_class($participant->user),
            'signed_by_id' => $participant->user->id,
            'signature_method' => 'electronic',
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signature_hash' => hash('sha256', $document->id.$participant->user->id.$request->ip().now()->timestamp),
        ];

        $signature = DocumentSignature::createSignedRecord(
            $document,
            null,
            $request->input('signature_image'),
            $signatureData
        );

        $document->update(['status' => 'signed']);

        if (class_exists(ParticipantDocumentSignature::class)) {
            ParticipantDocumentSignature::create([
                'participant_id' => $participant->id,
                'document_id' => $document->id,
                'signed_at' => now(),
                'signature_data' => $request->input('signature_image') ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // notify admins about signed form
        $admins = User::whereIn('role', ['admin', 'system_admin'])->get();
        foreach ($admins as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'type' => 'success',
                'data' => [
                    'title' => 'Onboarding form signed',
                    'message' => "{$participant->first_name} {$participant->last_name} signed '{$document->title}'",
                    'url' => route('portal.admin.documents.show', $document),
                ],
            ]);
        }

        AuditLogService::record('Onboarding Document Signed', $document, [], [
            'participant_id' => $participant->id,
            'document_id' => $document->id,
        ]);

        return redirect()->route('portal.onboarding.show', ['token' => $token])->with('status', 'Form signed successfully.');
    }

    public function submit(Request $request, string $token)
    {
        $participant = Participant::where('onboarding_token', $token)
            ->where('status', 'onboarding')
            ->whereNotNull('onboarding_expires_at')
            ->where('onboarding_expires_at', '>=', now())
            ->first();

        if (! $participant) {
            return redirect()->route('portal.login')
                ->withErrors(['token' => 'This onboarding link is invalid or has expired. Please contact AHHC support.']);
        }

        $progress = OnboardingProgress::firstOrCreate(
            ['participant_id' => $participant->id],
            [
                'current_step' => 1,
                'completed_steps' => [],
                'draft_data' => [],
                'status' => 'in_progress',
            ]
        );

        $saveDraft = $request->boolean('save_draft');
        $currentStep = max(1, min((int) $request->input('current_step', 1), 8));

        $rules = $saveDraft
            ? $this->validationRulesForCurrentStep($currentStep)
            : $this->validationRulesForStep($currentStep);

        $validated = $request->validate($rules);

        $draftData = array_merge($progress->draft_data ?? [], $this->collectDraftData($request));
        $progress->draft_data = $draftData;
        $progress->current_step = $currentStep;
        $progress->status = $saveDraft ? 'draft' : 'in_progress';
        $progress->completed_steps = range(1, $currentStep);
        $progress->save();

        if ($saveDraft) {
            return redirect()->route('portal.onboarding.show', ['token' => $token])
                ->with('status', 'Your onboarding draft has been saved. You can resume later from this link.');
        }

        $userData = [];
        if (isset($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $shouldCompleteOnboarding = $currentStep === 8;
        if ($shouldCompleteOnboarding) {
            // Final onboarding step completes the process, but activation requires admin review.
            $userData['status'] = 'inactive';
        }

        if (! empty($userData)) {
            $participant->user->update($userData);
        }

        $participantUpdates = [
            'preferred_name' => $validated['preferred_name'] ?? $participant->preferred_name,
            'phone' => $validated['phone'] ?? $participant->phone,
            'address' => $validated['address'] ?? $participant->address,
            'city' => $validated['city'] ?? $participant->city,
            'state' => $validated['state'] ?? $participant->state,
            'postcode' => $validated['postcode'] ?? $participant->postcode,
            'date_of_birth' => $validated['date_of_birth'] ?? $participant->date_of_birth,
            'primary_language' => $validated['primary_language'] ?? $participant->primary_language,
            'consent_to_share' => $request->boolean('agreement_consent'),
        ];

        if ($shouldCompleteOnboarding) {
            $participantUpdates['onboarding_token'] = null;
            $participantUpdates['onboarding_expires_at'] = null;
            // mark as pending admin review until an admin explicitly approves
            $participantUpdates['status'] = Participant::STATUS_PENDING_ADMIN_REVIEW;
        }

        $participant->update($participantUpdates);

        if ($currentStep >= 7) {
            $agreementService = new OnboardingAgreementService;
            foreach (OnboardingAgreementService::requiredAgreements() as $agreementKey => $agreementLabel) {
                $agreementService->createSignedAgreement(
                    $participant,
                    $agreementKey,
                    $validated['agreement_full_name'],
                    $validated['signature_image'],
                    $request->ip(),
                    $request->userAgent()
                );

                AuditLogService::record('Onboarding Agreement Signed', $participant, [], [
                    'agreement_key' => $agreementKey,
                    'agreement_name' => $agreementLabel,
                ]);
            }
        }

        if ($request->filled('support_first_name') && $request->filled('support_last_name')) {
            $supportPerson = SupportPerson::updateOrCreate(
                [
                    'user_id' => $participant->user->id,
                    'first_name' => $request->input('support_first_name'),
                    'last_name' => $request->input('support_last_name'),
                ],
                [
                    'relationship' => $request->input('support_relationship'),
                    'phone' => $request->input('support_phone'),
                    'email' => $request->input('support_email'),
                    'address' => $request->input('support_address'),
                    'city' => $request->input('support_city'),
                    'state' => $request->input('support_state'),
                    'postcode' => $request->input('support_postcode'),
                ]
            );

            $participant->update(['assigned_support_person_id' => $supportPerson->id]);
        }

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $documentType = Document::normalizeParticipantDocumentCategory($request->input('document_type') ?? 'Onboarding Document');

            $doc = Document::create([
                'owner_type' => Participant::class,
                'owner_id' => $participant->id,
                'document_type' => $documentType,
                'title' => $request->input('document_title') ?? $file->getClientOriginalName(),
                'description' => $request->input('document_description') ?? null,
                'storage_disk' => 'local',
                'path' => $file->store('documents', 'local'),
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'uploaded_by_id' => $participant->user->id,
                'status' => 'uploaded',
            ]);

            // notify admins of document upload/resubmission
            $admins = User::whereIn('role', ['admin', 'system_admin'])->get();
            foreach ($admins as $admin) {
                NotificationCenterService::send('document_resubmitted', $admin->id, [
                    'title' => 'Document uploaded',
                    'message' => "{$participant->first_name} {$participant->last_name} uploaded a document: {$doc->title}",
                    'participant_id' => $participant->id,
                ]);
            }
        }

        if ($shouldCompleteOnboarding) {
            // Only the profile step and agreement step are mandatory for completion.
            // Emergency contacts, support person details, and uploaded documents remain optional.
            $requiredAgreements = array_values(OnboardingAgreementService::requiredAgreements());
            $signedAgreementDocs = Document::query()
                ->where('owner_type', Participant::class)
                ->where('owner_id', $participant->id)
                ->where('status', 'signed')
                ->whereIn('document_type', $requiredAgreements)
                ->pluck('document_type')
                ->unique();

            $missingAgreements = collect($requiredAgreements)->diff($signedAgreementDocs)->values();
            if ($missingAgreements->isNotEmpty()) {
                return back()->withErrors([
                    'agreements' => 'Please sign the required onboarding agreements: '.$missingAgreements->implode(', '),
                ])->withInput();
            }

            // mark onboarding progress complete and keep participant pending admin review
            $progress->current_step = 8;
            $progress->completed_steps = range(1, 8);
            $progress->status = 'complete';
            $progress->completed_at = now();
            $progress->draft_data = [];
            $progress->save();

            AuditLogService::record('Participant Onboarding Submitted', $participant, [], [
                'participant_id' => $participant->id,
                'user_id' => $participant->user->id,
            ]);

            // notify admins for review
            $admins = User::whereIn('role', ['admin', 'system_admin'])->get();
            foreach ($admins as $admin) {
                NotificationCenterService::send(
                    'onboarding_submitted',
                    $admin->id,
                    [
                        'title' => 'Onboarding submitted',
                        'message' => "{$participant->first_name} {$participant->last_name} submitted onboarding for review.",
                        'participant_id' => $participant->id,
                        'submission_id' => $progress->id,
                        'submitted_at' => now()->toDateTimeString(),
                    ]
                );
            }

            return redirect()->route('portal.onboarding.status')->with('status', 'Onboarding submitted and is pending admin review before your account can be activated.');
        }

        return redirect()->route('portal.onboarding.show', ['token' => $token])
            ->with('status', 'Your onboarding progress has been saved. Continue when you are ready.');
    }

    protected function validationRulesForCurrentStep(int $currentStep): array
    {
        return match ($currentStep) {
            1 => [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            2 => [
                'mfa_acknowledged' => ['sometimes', 'boolean'],
            ],
            3 => [
                'preferred_name' => ['required', 'string', 'max:100'],
                'phone' => ['required', 'string', 'max:50'],
            ],
            4 => [
                'emergency_contact_name' => ['required', 'string', 'max:100'],
                'emergency_contact_relationship' => ['required', 'string', 'max:100'],
                'emergency_contact_phone' => ['required', 'string', 'max:50'],
                'emergency_contact_email' => ['nullable', 'email', 'max:150'],
            ],
            5 => [
                'support_first_name' => ['nullable', 'string', 'max:100'],
                'support_last_name' => ['nullable', 'string', 'max:100'],
                'support_email' => ['nullable', 'email', 'max:150'],
                'support_phone' => ['nullable', 'string', 'max:50'],
                'support_relationship' => ['nullable', 'string', 'max:100'],
                'support_address' => ['nullable', 'string', 'max:255'],
                'support_city' => ['nullable', 'string', 'max:100'],
                'support_state' => ['nullable', 'string', 'max:100'],
                'support_postcode' => ['nullable', 'string', 'max:50'],
            ],
            6 => [
                'document_type' => ['nullable', 'string', 'max:100'],
                'document_title' => ['nullable', 'string', 'max:150'],
                'document_description' => ['nullable', 'string', 'max:255'],
                'document_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv', 'max:10240'],
            ],
            7 => [
                'agreement_self_management' => ['accepted'],
                'agreement_privacy' => ['accepted'],
                'agreement_responsibilities' => ['accepted'],
                'agreement_terms' => ['accepted'],
                'agreement_full_name' => ['required', 'string', 'max:150'],
                'signature_image' => ['required', 'string'],
            ],
            8 => array_merge(
                $this->validationRulesForCurrentStep(3),
                $this->validationRulesForCurrentStep(7),
            ),
            default => [],
        };
    }

    protected function validationRulesForStep(int $currentStep): array
    {
        if ($currentStep === 8) {
            return $this->validationRulesForCurrentStep(8);
        }

        return $this->validationRulesForCurrentStep($currentStep);
    }

    protected function collectDraftData(Request $request): array
    {
        return array_filter($request->only([
            'preferred_name',
            'phone',
            'address',
            'city',
            'state',
            'postcode',
            'date_of_birth',
            'primary_language',
            'support_relationship',
            'support_first_name',
            'support_last_name',
            'support_phone',
            'support_email',
            'support_address',
            'support_city',
            'support_state',
            'support_postcode',
            'emergency_contact_name',
            'emergency_contact_relationship',
            'emergency_contact_phone',
            'emergency_contact_email',
            'document_title',
            'document_description',
            'agreement_self_management',
            'agreement_terms',
            'agreement_privacy',
            'agreement_responsibilities',
            'agreement_consent',
            'agreement_full_name',
            'signature_image',
            'mfa_acknowledged',
        ]), fn ($value) => ! is_null($value) && $value !== '');
    }

    public function isMfaRequiredForOnboarding(): bool
    {
        $setting = PortalSetting::where('key', 'require_mfa')->first();

        if (! $setting) {
            return false;
        }

        return (bool) $setting->value;
    }

    /**
     * Show onboarding status page for authenticated participants
     */
    public function status()
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'participant' || ! $user->participant) {
            return redirect()->route('portal.login');
        }

        $participant = $user->participant;

        // If participant is active, redirect to dashboard
        if ($participant->status === Participant::STATUS_ACTIVE) {
            return redirect()->route('portal.dashboard');
        }

        if ($participant->status === Participant::STATUS_PENDING_ADMIN_REVIEW) {
            return redirect()->route('portal.participant.documents.index');
        }

        // Determine status message
        $statusMessage = match ($participant->status) {
            Participant::STATUS_ONBOARDING => 'Please complete your onboarding using the invitation link sent to your email.',
            Participant::STATUS_PENDING_ADMIN_REVIEW => 'Your onboarding has been submitted and is awaiting AHHC review. We will contact you shortly.',
            Participant::STATUS_AHHC_REVIEW => 'Your application is currently being reviewed by AHHC. We will update you as soon as possible.',
            Participant::STATUS_ELIGIBILITY_ASSESSMENT => 'Your eligibility is being assessed. We will be in touch soon.',
            Participant::STATUS_SUITABILITY_ASSESSMENT => 'Your suitability assessment is in progress. We will update you shortly.',
            default => 'Your account is being processed. Please check back soon.',
        };

        $onboardingToken = $participant->onboarding_token;
        $onboardingExpiresAt = $participant->onboarding_expires_at;
        $onboardingTokenValid = $onboardingToken && $onboardingExpiresAt && $onboardingExpiresAt->isFuture();

        $progress = OnboardingProgress::where('participant_id', $participant->id)->first();
        $onboardingProgress = $progress?->completionPercentage() ?? 0;

        return view('auth.onboarding-status', compact(
            'participant',
            'statusMessage',
            'onboardingToken',
            'onboardingTokenValid',
            'onboardingProgress'
        ));
    }
}
