<?php

namespace App\Http\Controllers;

use App\Enums\WorkerDeclarationType;
use App\Models\Document;
use App\Models\PortalSetting;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerComplianceType;
use App\Models\WorkerDeclaration;
use App\Services\NotificationService;
use App\Services\TemplateMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class WorkerOnboardingController extends Controller
{
    /**
     * Show the worker onboarding page for a token
     */
    public function show(string $token)
    {
        $worker = Worker::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_expires_at')
                    ->orWhere('onboarding_expires_at', '>', now());
            })
            ->firstOrFail();

        $stage = $worker->getCurrentStage();
        $stageNumber = $worker->onboarding_stage;

        if ($stageNumber === 2 && $worker->stage_2_submitted_at) {
            return $this->showStage3($worker, $token);
        }

        // Get appropriate view based on stage
        return match ($stageNumber) {
            1 => $this->showStage1($worker, $token),
            2 => $this->showStage2($worker, $token),
            3 => $this->showStage3($worker, $token),
            4 => $this->showStage4($worker, $token),
            5 => $this->showStage5($worker, $token),
            6 => $this->showStage6($worker, $token),
            default => abort(400, 'Invalid onboarding stage'),
        };
    }

    /**
     * Stage 1: Account creation and MFA setup (typically auto-created by admin)
     */
    private function showStage1(Worker $worker, string $token)
    {
        // If user account doesn't exist yet, show account setup form
        if (! $worker->user) {
            return view('portal.worker.onboarding.stage1_create', [
                'worker' => $worker,
                'token' => $token,
            ]);
        }

        if (! $this->isMfaRequiredGlobally()) {
            if ($worker->onboarding_stage === 1) {
                $worker->update([
                    'stage_1_completed_at' => now(),
                    'onboarding_stage' => 2,
                ]);
            }

            return redirect()->route('worker.onboarding.show', ['token' => $token]);
        }

        // If the user has completed MFA or MFA is not required, advance to Stage 2 automatically.
        if (
            $worker->user &&
            $worker->onboarding_stage === 1 &&
            (
                ! $this->isMfaRequiredGlobally() ||
                $worker->user->mfa_enabled
            )
        ) {
            $worker->update([
                'stage_1_completed_at' => now(),
                'onboarding_stage' => 2,
            ]);

            return redirect()->route('worker.onboarding.show', ['token' => $token]);
        }

        // User account already exists. Show the worker onboarding status page so the worker understands
        // they must complete MFA setup and wait for admin progression to Stage 2.
        return view('portal.worker.onboarding.stage1_status', [
            'worker' => $worker,
            'token' => $token,
            'mfaEnabled' => $worker->user->mfa_enabled,
            'mfaRequired' => $this->isMfaRequiredGlobally(),
        ]);
    }

    private function isMfaRequiredGlobally(): bool
    {
        $setting = PortalSetting::where('key', 'require_mfa')->first();

        if (! $setting) {
            return false;
        }

        return (bool) $setting->value;
    }

    /**
     * Handle Stage 1 account creation
     */
    public function submitStage1(Request $request, string $token)
    {
        $worker = Worker::where('onboarding_token', $token)->firstOrFail();

        if ($worker->onboarding_stage !== 1 || $worker->user) {
            abort(403, 'Invalid onboarding state');
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults()],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $user = User::where('email', $worker->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => "{$worker->first_name} {$worker->last_name}",
                'email' => $worker->email,
                'phone' => $worker->phone,
                'role' => 'worker',
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);
        } else {
            if ($user->role !== 'worker') {
                $user->role = 'worker';
            }

            $user->password = Hash::make($validated['password']);
            $user->status = 'active';
            $user->save();
        }

        $worker->update(['user_id' => $user->id]);

        // Send account-activated email prompting the worker to sign in and continue onboarding.
        if (is_string($worker->email ?? null) && filter_var($worker->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($worker->email)->send(new \App\Mail\AccountActivated($user));
            } catch (\Throwable $e) {
                // Log error but don't block onboarding flow if email fails
                \Log::error('Failed to send account activated email', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('portal.login')->with('success', 'Account created successfully. Please log in to continue with your onboarding.');
    }

    /**
     * Stage 2: Upload compliance documents
     */
    private function showStage2(Worker $worker, string $token)
    {
        $complianceTypes = $this->getStage2ComplianceRequirements();

        return view('portal.worker.onboarding.stage2_compliance', [
            'worker' => $worker,
            'token' => $token,
            'complianceTypes' => $complianceTypes,
            'uploadedDocuments' => $worker->complianceDocuments()->get(),
        ]);
    }

    /**
     * Handle Stage 2 compliance upload
     */
    public function submitStage2(Request $request, string $token)
    {
        $worker = Worker::where('onboarding_token', $token)->firstOrFail();

        if ($worker->onboarding_stage !== 2) {
            abort(403, 'Invalid onboarding state');
        }

        $requirements = $this->getStage2ComplianceRequirements();
        $validationRules = [
            'abn_number' => ['nullable', 'string', 'max:50'],
        ];

        foreach ($requirements as $requirement) {
            $field = 'documents.'.$requirement['slug'];
            $validationRules[$field] = $requirement['required']
                ? ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx']
                : ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'];
        }

        $validated = $request->validate($validationRules);

        DB::transaction(function () use ($worker, $request, $requirements, $validated) {
            WorkerComplianceType::ensureDefaults();

            $hasAnyUploadedDocument = false;

            foreach ($requirements as $requirement) {
                if (! $request->hasFile('documents.'.$requirement['slug'])) {
                    continue;
                }

                $document = $request->file('documents.'.$requirement['slug']);
                $path = $document->store('worker_compliance/'.$worker->id, 'private');

                $typeRecord = WorkerComplianceType::where('name', $requirement['name'])->first();

                $worker->complianceDocuments()->updateOrCreate(
                    ['document_type' => $requirement['name']],
                    [
                        'worker_compliance_type_id' => $typeRecord?->id,
                        'document_path' => $path,
                        'status' => 'submitted',
                    ]
                );

                $hasAnyUploadedDocument = true;
            }

            $abnNumber = $validated['abn_number'] ?? null;

            $update = [
                'stage_2_submitted_at' => now(),
            ];

            if ($abnNumber) {
                $update['abn_number'] = $abnNumber;
            }

            $worker->update($update);
        });

        if ($worker->user_id) {
            NotificationService::notify([
                'user_id' => $worker->user_id,
                'title' => 'Compliance Documents Submitted',
                'message' => 'Your compliance documents have been submitted for review. AHHC will notify you once they have been reviewed.',
                'type' => 'info',
                'channel' => 'in_app',
                'data' => ['worker_id' => $worker->id],
            ]);
        }

        $adminUsers = User::whereIn('role', ['admin', 'system_admin'])->get();
        foreach ($adminUsers as $adminUser) {
            NotificationService::notify([
                'user_id' => $adminUser->id,
                'title' => 'Worker Submitted Compliance Documents',
                'message' => sprintf('%s %s has uploaded Stage 2 compliance documents for review.', $worker->first_name, $worker->last_name),
                'type' => 'info',
                'channel' => 'in_app',
                'data' => [
                    'worker_id' => $worker->id,
                    'url' => route('admin.worker_onboarding.show', $worker),
                ],
            ]);
        }

        return redirect()->route('worker.onboarding.show', ['token' => $token])
            ->with('success', 'Compliance documents submitted successfully. AHHC will review them and notify you when complete.');
    }

    /**
     * Stage 3: View document review status (read-only for worker)
     */
    private function showStage3(Worker $worker, string $token)
    {
        $documents = $worker->complianceDocuments()->get();
        $allRequiredUploaded = $this->areAllRequiredStage2DocumentsUploaded($worker);

        return view('portal.worker.onboarding.stage3_review', [
            'worker' => $worker,
            'token' => $token,
            'documents' => $documents,
            'canProceed' => $allRequiredUploaded,
        ]);
    }

    public function proceedStage3(string $token)
    {
        $worker = Worker::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_expires_at')
                    ->orWhere('onboarding_expires_at', '>', now());
            })
            ->firstOrFail();

        if (! in_array($worker->onboarding_stage, [2, 3], true)) {
            abort(403, 'Invalid onboarding state');
        }

        if (! $this->areAllRequiredStage2DocumentsUploaded($worker)) {
            return back()->withErrors(['documents' => 'Please upload all required compliance documents before proceeding.']);
        }

        $this->createDeclarationsForWorker($worker);

        $worker->update([
            'stage_2_completed_at' => $worker->stage_2_completed_at ?? now(),
            'stage_3_submitted_at' => $worker->stage_3_submitted_at ?? now(),
            'stage_3_completed_at' => now(),
            'onboarding_stage' => 4,
        ]);

        return redirect()->route('worker.onboarding.show', ['token' => $token])
            ->with('success', 'All required compliance documents are uploaded. You may now sign the declarations.');
    }

    private function areAllRequiredStage2DocumentsUploaded(Worker $worker): bool
    {
        $requirements = $this->getStage2ComplianceRequirements();
        $requiredNames = collect($requirements)->where('required', true)->pluck('name');

        $uploadedNames = $worker->complianceDocuments()
            ->whereIn('document_type', $requiredNames)
            ->pluck('document_type')
            ->unique();

        return $requiredNames->diff($uploadedNames)->isEmpty();
    }

    public function previewDocument(string $token, WorkerComplianceDocument $document)
    {
        $worker = Worker::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_expires_at')
                    ->orWhere('onboarding_expires_at', '>', now());
            })
            ->firstOrFail();

        if ($document->worker_id !== $worker->id) {
            abort(403, 'Document does not belong to this onboarding worker.');
        }

        if (! $document->document_path || ! Storage::disk('private')->exists($document->document_path)) {
            abort(404, 'File not found.');
        }

        return response()->file(Storage::disk('private')->path($document->document_path), [
            'Content-Type' => Storage::disk('private')->mimeType($document->document_path),
            'Content-Disposition' => 'inline; filename="'.basename($document->document_path).'"',
        ]);
    }

    public function downloadDocument(string $token, WorkerComplianceDocument $document)
    {
        $worker = Worker::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_expires_at')
                    ->orWhere('onboarding_expires_at', '>', now());
            })
            ->firstOrFail();

        if ($document->worker_id !== $worker->id) {
            abort(403, 'Document does not belong to this onboarding worker.');
        }

        if (! $document->document_path || ! Storage::disk('private')->exists($document->document_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download($document->document_path, basename($document->document_path));
    }

    public function previewAssignedDocument(string $token, Document $document)
    {
        $worker = Worker::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_expires_at')
                    ->orWhere('onboarding_expires_at', '>', now());
            })
            ->firstOrFail();

        if ($document->owner_type !== Worker::class || $document->owner_id !== $worker->id) {
            abort(403, 'This document is not assigned to this worker.');
        }

        if (! $document->onboarding_required || $document->status !== 'active') {
            abort(404, 'Document not available for onboarding.');
        }

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404, 'File not found.');
        }

        return response()->file(Storage::disk($document->storage_disk)->path($document->path), [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.$document->title.'"',
        ]);
    }

    public function downloadAssignedDocument(string $token, Document $document)
    {
        $worker = Worker::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_expires_at')
                    ->orWhere('onboarding_expires_at', '>', now());
            })
            ->firstOrFail();

        if ($document->owner_type !== Worker::class || $document->owner_id !== $worker->id) {
            abort(403, 'This document is not assigned to this worker.');
        }

        if (! $document->onboarding_required || $document->status !== 'active') {
            abort(404, 'Document not available for onboarding.');
        }

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    /**
     * Stage 4: Sign declarations
     */
    private function showStage4(Worker $worker, string $token)
    {
        $adminAssignedDocuments = Document::where('owner_type', Worker::class)
            ->where('owner_id', $worker->id)
            ->where('onboarding_required', true)
            ->where('status', 'active')
            ->get();

        $declarations = WorkerDeclaration::where('worker_id', $worker->id)->get();
        if ($declarations->isEmpty()) {
            $this->createDeclarationsForWorker($worker);
            $declarations = WorkerDeclaration::where('worker_id', $worker->id)->get();
        }

        return view('portal.worker.onboarding.stage4_declarations', [
            'worker' => $worker,
            'token' => $token,
            'declarations' => $declarations,
            'adminAssignedDocuments' => $adminAssignedDocuments,
        ]);
    }

    /**
     * Handle Stage 4 declaration signing
     */
    public function submitStage4(Request $request, string $token)
    {
        $worker = Worker::where('onboarding_token', $token)->firstOrFail();

        if ($worker->onboarding_stage !== 4) {
            abort(403, 'Invalid onboarding state');
        }

        $validated = $request->validate([
            'declarations' => ['required', 'array'],
            'declarations.*' => ['required', 'in:yes,no'],
            'signature_image' => ['required', 'string'],
        ]);

        $allAgreed = collect($validated['declarations'])->every(fn ($v) => $v === 'yes');

        if (! $allAgreed) {
            return back()->withErrors(['declarations' => 'You must agree to all declarations to proceed.']);
        }

        DB::transaction(function () use ($worker, $request, $validated) {
            foreach ($request->input('declarations') as $declarationType => $agreed) {
                $declaration = WorkerDeclaration::where('worker_id', $worker->id)
                    ->where('declaration_type', $declarationType)
                    ->firstOrFail();

                $update = ['agreed' => $agreed === 'yes'];

                if ($agreed === 'yes') {
                    $update['signed_at'] = now();

                    $signatureImage = $validated['signature_image'];
                    if ($signatureImage && strpos($signatureImage, 'base64,') !== false) {
                        [$meta, $content] = explode(',', $signatureImage, 2);
                        $decoded = base64_decode($content);
                        if ($decoded !== false) {
                            $filename = 'worker_signatures/'.$worker->id.'/'.time().'_'.uniqid().'.png';
                            Storage::disk('private')->put($filename, $decoded);
                            $update['signature_file_path'] = $filename;
                        }
                    }
                } else {
                    $update['declined_at'] = now();
                }

                $declaration->update($update);
            }

            $worker->update([
                'stage_4_submitted_at' => now(),
                'stage_4_completed_at' => now(),
                'onboarding_stage' => 5,
            ]);
        });

        return redirect()->route('worker.onboarding.show', ['token' => $token])
            ->with('success', 'Declarations signed successfully.');
    }

    /**
     * Stage 5: View approved services (read-only for worker)
     */
    private function createDeclarationsForWorker(Worker $worker): void
    {
        foreach (WorkerDeclarationType::all() as $declarationType) {
            WorkerDeclaration::firstOrCreate(
                [
                    'worker_id' => $worker->id,
                    'declaration_type' => $declarationType,
                ],
                [
                    'declaration_text' => $declarationType->defaultText(),
                    'agreed' => false,
                ]
            );
        }
    }

    private function showStage5(Worker $worker, string $token)
    {
        $approvedServices = $worker->serviceApprovals()
            ->where('status', 'approved')
            ->get();

        return view('portal.worker.onboarding.stage5_services', [
            'worker' => $worker,
            'token' => $token,
            'approvedServices' => $approvedServices,
        ]);
    }

    /**
     * Stage 6: Assignment complete (read-only)
     */
    private function showStage6(Worker $worker, string $token)
    {
        $assignments = $worker->assignments()
            ->where('status', 'active')
            ->with('participant')
            ->get();

        return view('portal.worker.onboarding.stage6_complete', [
            'worker' => $worker,
            'token' => $token,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Get Stage 2 compliance document requirements
     */
    private function getStage2ComplianceRequirements(): array
    {
        return [
            // ABN is collected as a number field, not an uploaded document.
            [
                'slug' => 'police_check',
                'name' => 'Police Check',
                'required' => true,
                'description' => 'Valid police clearance document.',
            ],
            [
                'slug' => 'ndis_worker_screening',
                'name' => 'NDIS Worker Screening',
                'required' => false,
                'description' => 'NDIS worker screening certificate.',
            ],
            [
                'slug' => 'insurance',
                'name' => 'Insurance',
                'required' => false,
                'description' => 'Professional indemnity or public liability insurance.',
            ],
            [
                'slug' => 'qualification',
                'name' => 'Qualification',
                'required' => false,
                'description' => 'Relevant qualifications and certifications.',
            ],
            [
                'slug' => 'first_aid_certificate',
                'name' => 'First Aid Certificate',
                'required' => false,
                'description' => 'First Aid certification evidence.',
            ],
            [
                'slug' => 'cpr_certificate',
                'name' => 'CPR Certificate',
                'required' => false,
                'description' => 'CPR training certification.',
            ],
            [
                'slug' => 'registration',
                'name' => 'APHRA Registration',
                'required' => false,
                'description' => 'Professional registration or licensing documents.',
            ],
            [
                'slug' => 'marketplace_agreement',
                'name' => 'Marketplace Agreement',
                'required' => false,
                'description' => 'Marketplace agreement if applicable.',
            ],
        ];
    }
}
