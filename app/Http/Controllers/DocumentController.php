<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentVersion;
use App\Models\Participant;
use App\Models\ParticipantDocumentSignature;
use App\Models\SignatureRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function indexForParticipant()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $documents = Document::query()
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->latest()
            ->get();

        // Get uploaded categories as display names (e.g. "Care Plan") for comparison with required categories
        $uploadedCategories = $documents->pluck('document_type')
            ->map(fn ($type) => Document::denormalizeParticipantDocumentCategory($type))
            ->unique();
        $requiredCategories = Document::mandatoryParticipantDocumentCategories();
        $missingMandatory = collect($requiredCategories)->diff($uploadedCategories)->values();
        $mandatoryCompletion = round((count($requiredCategories) - $missingMandatory->count()) / max(count($requiredCategories), 1) * 100);

        return view('portal.participant.documents', compact('participant', 'documents', 'uploadedCategories', 'missingMandatory', 'requiredCategories', 'mandatoryCompletion'));
    }

    public function pendingSignaturesForParticipant()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $signatureRequests = SignatureRequest::with('document')
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', [SignatureRequest::STATUS_PENDING, SignatureRequest::STATUS_VIEWED])
            ->latest('assigned_at')
            ->get();

        return view('portal.participant.pending-signatures', compact('participant', 'signatureRequests'));
    }

    public function storeForParticipant(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string'],
            'file' => Document::fileValidationRules(),
        ]);

        // normalize document_type: accept either display names or snake_case keys and store snake_case key
        $docTypeInput = $validated['document_type'];
        $docKey = Document::normalizeParticipantDocumentCategory($docTypeInput);

        // Accept any document_type key (snake_case) or display name;
        // normalize to canonical display category labels for checklist matching.

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => $docKey,
            'title' => $validated['title'],
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'is_sensitive' => true,
        ]);

        $document->versions()->create([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'version_number' => 1,
        ]);

        AuditLogService::record('Document Upload', $document, [], [
            'title' => $document->title,
            'document_type' => $document->document_type,
            'owner_id' => $document->owner_id,
            'owner_type' => $document->owner_type,
        ]);

        User::where('role', 'admin')->get()->each(function ($admin) use ($participant, $validated) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'participant_id' => $participant->id,
                'type' => 'info',
                'data' => [
                    'title' => 'New document uploaded',
                    'message' => "{$participant->first_name} uploaded a new {$validated['document_type']} document.",
                    'url' => route('portal.admin.documents'),
                ],
            ]);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Document uploaded successfully.',
                'document' => $document->load('uploader')->toArray(),
            ], 201);
        }

        // Redirect participants to dashboard for budget plan uploads, otherwise to their documents list
        if ($docKey === 'budget_plan') {
            return redirect()->route('portal.dashboard')
                ->with('success', 'Your document has been uploaded successfully. Our admin team will review it shortly. Please wait for their response.');
        }

        return redirect()->route('portal.participant.documents.index')
            ->with('success', 'Your document has been uploaded successfully. Our admin team will review it shortly. Please wait for their response.');
    }

    public function previewForParticipant(Document $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->where('id', $document->id)
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->firstOrFail();

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        AuditLogService::record('Document Previewed', $document, [], []);

        if (! $document->isPreviewable()) {
            return $this->download($document);
        }

        $filePath = Storage::disk($document->storage_disk)->path($document->path);

        return response()->file($filePath, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.$document->title.'"',
        ]);
    }

    public function download(Document $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->where('id', $document->id)
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->firstOrFail();

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        AuditLogService::record('Document Download', $document, [], []);

        $disk = Storage::disk($document->storage_disk);
        $filePath = $disk->path($document->path);
        $filename = $document->title;

        return response()->download($filePath, $filename);
    }

    public function downloadVersionForParticipant(Document $document, DocumentVersion $version)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->where('id', $document->id)
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->firstOrFail();

        if ($version->document_id !== $document->id) {
            abort(404);
        }

        if (! Storage::disk($version->storage_disk)->exists($version->path)) {
            abort(404);
        }

        $filename = $document->title.'_v'.$version->version_number.'.'.pathinfo($version->path, PATHINFO_EXTENSION);

        return Storage::disk($version->storage_disk)->download($version->path, $filename);
    }

    public function showForParticipant(Document $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->with(['signatures.signedBy', 'signatureRequests.assignedBy', 'signatureRequests.assignedUser'])
            ->where('id', $document->id)
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->firstOrFail();

        $signatureRequest = SignatureRequest::where('document_id', $document->id)
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', [SignatureRequest::STATUS_PENDING, SignatureRequest::STATUS_VIEWED])
            ->latest('assigned_at')
            ->first();

        if ($signatureRequest) {
            $signatureRequest->markViewed();
            AuditLogService::record('Signature Request Viewed', $signatureRequest, [], [
                'status' => $signatureRequest->status,
                'document_id' => $document->id,
            ]);
        }

        return view('portal.participant.document', compact('document', 'signatureRequest'));
    }

    public function uploadVersionForParticipant(Request $request, Document $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->where('id', $document->id)
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'file' => Document::fileValidationRules(),
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $document->addVersionFromUploadedFile($request->file('file'), $user, $validated['notes'] ?? null);

        AuditLogService::record('Document Version Uploaded', $document, [], [
            'document_id' => $document->id,
            'version_count' => $document->versions()->count(),
            'uploaded_by' => $user->id,
        ]);

        return back()->with('status', 'New document version uploaded successfully.');
    }

    public function signForParticipant(Request $request, Document $document)
    {
        $request->validate([
            'confirm_signature' => ['accepted'],
            'signature_image' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->with('signatures')
            ->where('id', $document->id)
            ->firstOrFail();

        // allow signing if this document belongs to the participant OR it's a system/admin form assigned to onboarding
        $isOwnerParticipant = $document->owner_type === Participant::class && $document->owner_id === $participant->id;
        if (! $isOwnerParticipant && ! ($document->onboarding_required && $document->status === 'active')) {
            return back()->with('status', 'You are not permitted to sign this document.');
        }

        if ($document->signatures()->exists()) {
            return back()->with('status', 'This document has already been signed.');
        }

        $signatureRequest = SignatureRequest::where('document_id', $document->id)
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', [SignatureRequest::STATUS_PENDING, SignatureRequest::STATUS_VIEWED])
            ->latest('assigned_at')
            ->first();

        if (! $signatureRequest && ! $isOwnerParticipant) {
            return back()->with('status', 'No signature request is available for this document.');
        }

        $signatureData = [
            'signed_by_type' => get_class($user),
            'signed_by_id' => $user->id,
            'signature_request_id' => $signatureRequest?->id ?? null,
            'signature_method' => 'electronic',
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signature_hash' => hash('sha256', $document->id.$user->id.$request->ip().now()->timestamp),
        ];

        $signature = DocumentSignature::createSignedRecord(
            $document,
            $signatureRequest,
            $request->input('signature_image'),
            $signatureData
        );

        if ($signatureRequest) {
            $signatureRequest->markSigned();
        }

        $document->update(['status' => 'signed']);

        // record participant-document signature for onboarding-tracked documents
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

        NotificationService::notify([
            'user_id' => $signatureRequest->assigned_by ?? $user->id,
            'type' => 'success',
            'data' => [
                'title' => 'Document signed',
                'message' => "{$user->name} signed '{$document->title}' successfully.",
                'url' => route('portal.admin.documents.show', $document),
            ],
        ]);

        NotificationService::notify([
            'user_id' => $user->id,
            'type' => 'success',
            'data' => [
                'title' => 'Signature confirmed',
                'message' => "You signed '{$document->title}' and a signed copy is now stored.",
                'url' => route('portal.participant.documents.show', $document),
            ],
        ]);

        AuditLogService::record('Document Signed', $signatureRequest ?? $signature, [], [
            'signature_id' => $signature->id,
            'document_id' => $document->id,
            'assigned_user_id' => $signatureRequest->assigned_user_id ?? $user->id,
            'status' => $signatureRequest->status ?? 'signed',
        ]);

        return redirect()->route('portal.participant.documents.show', $document)->with('status', 'Document signed successfully.');
    }

    public function gallery()
    {
        $documents = Document::query()
            ->with('owner')
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('portal.gallery', compact('documents'));
    }

    public function previewGallery(Document $document)
    {
        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return response()->file(Storage::disk($document->storage_disk)->path($document->path), [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.$document->title.'"',
        ]);
    }

    public function downloadGallery(Document $document)
    {
        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    public function downloadSignature(DocumentSignature $signature)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $signature->load('document');
        $document = $signature->document;

        if (! $document || $document->owner_type !== Participant::class || $document->owner_id !== $participant->id) {
            abort(403);
        }

        if (! $signature->signature_path) {
            abort(404);
        }

        return Storage::disk($signature->signature_disk ?? 'local')->download($signature->signature_path, $document->title.'_signature.png');
    }
}
