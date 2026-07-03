<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ParticipantDocument;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ParticipantDocumentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $documents = ParticipantDocument::query()
            ->where('participant_id', $participant->id)
            ->with('latestVersion', 'versions', 'uploader')
            ->latest()
            ->get();

        $pendingMandatory = collect(ParticipantDocument::MANDATORY_CATEGORIES)
            ->diff($documents->pluck('category')->unique())
            ->values();

        return view('portal.participant.document-center', compact('participant', 'documents', 'pendingMandatory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:'.implode(',', array_map(fn ($item) => addcslashes($item, ','), ParticipantDocument::MANDATORY_CATEGORIES).',Support Plan,Referral Documents,Authority Documents,Funding Documents,Identification')],
            'file' => ParticipantDocument::fileValidationRules(),
        ]);

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();
        $file = $request->file('file');

        $path = $file->store('participant_documents', 'local');

        $document = ParticipantDocument::create([
            'participant_id' => $participant->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);

        $document->versions()->create([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'version_number' => 1,
        ]);

        AuditLogService::record('Participant Document Uploaded', $document, [], [
            'category' => $document->category,
            'title' => $document->title,
            'participant_id' => $participant->id,
        ]);

        NotificationService::notify([
            'user_id' => $user->id,
            'participant_id' => $participant->id,
            'type' => 'success',
            'data' => [
                'title' => 'Document uploaded',
                'message' => "{$document->title} was uploaded successfully.",
                'url' => route('portal.participant.document_center'),
            ],
        ]);

        return redirect()->route('portal.participant.document_center')->with('status', 'Document uploaded successfully.');
    }

    public function download(ParticipantDocument $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($document->participant_id !== $participant->id) {
            abort(403);
        }

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    public function uploadVersion(Request $request, ParticipantDocument $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($document->participant_id !== $participant->id) {
            abort(403);
        }

        $validated = $request->validate([
            'file' => ParticipantDocument::fileValidationRules(),
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        $path = $file->store('participant_documents', 'local');
        $versionNumber = ($document->versions()->max('version_number') ?? 0) + 1;

        $document->versions()->create([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'version_number' => $versionNumber,
            'notes' => $validated['notes'] ?? null,
        ]);

        $document->update([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
        ]);

        AuditLogService::record('Participant Document Version Uploaded', $document, [], [
            'participant_document_id' => $document->id,
            'version_number' => $versionNumber,
        ]);

        return back()->with('status', 'New version uploaded successfully.');
    }

    public function downloadVersion(ParticipantDocument $document, $versionId)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($document->participant_id !== $participant->id) {
            abort(403);
        }

        $version = $document->versions()->findOrFail($versionId);

        if (! Storage::disk($version->storage_disk)->exists($version->path)) {
            abort(404);
        }

        $filename = $document->title.'_v'.$version->version_number.'.'.pathinfo($version->path, PATHINFO_EXTENSION);

        return Storage::disk($version->storage_disk)->download($version->path, $filename);
    }
}
