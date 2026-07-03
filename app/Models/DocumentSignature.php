<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class DocumentSignature extends Model
{
    protected $fillable = [
        'document_id',
        'signed_by_type',
        'signed_by_id',
        'signature_request_id',
        'signature_method',
        'signed_at',
        'ip_address',
        'user_agent',
        'signature_hash',
        'signature_path',
        'signature_disk',
        'signed_document_path',
        'signed_document_disk',
        'certificate_path',
        'certificate_disk',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function signedBy(): MorphTo
    {
        return $this->morphTo();
    }

    public static function createSignedRecord(Document $document, ?SignatureRequest $request, ?string $signatureImage, array $signatureData = []): self
    {
        $signedDocumentPath = null;
        $signedDocumentDisk = 'local';

        if (Storage::disk($document->storage_disk)->exists($document->path)) {
            $filename = pathinfo($document->path, PATHINFO_FILENAME);
            $extension = pathinfo($document->path, PATHINFO_EXTENSION);
            $signedDocumentPath = 'signed_documents/'.$filename.'_signed_'.time().'.'.$extension;
            $content = Storage::disk($document->storage_disk)->get($document->path);
            Storage::disk($signedDocumentDisk)->put($signedDocumentPath, $content);
        }

        if ($signatureImage && str_contains($signatureImage, 'base64,')) {
            [$meta, $encoded] = explode(',', $signatureImage, 2);
            $decoded = base64_decode($encoded);
            if ($decoded !== false) {
                $signaturePath = 'signatures/'.$document->id.'_'.time().'_'.uniqid().'.png';
                Storage::disk('local')->put($signaturePath, $decoded);
                $signatureData['signature_path'] = $signaturePath;
                $signatureData['signature_disk'] = 'local';
            }
        }

        $signatureData = array_merge([
            'document_id' => $document->id,
            'signature_request_id' => $request?->id,
            'signed_document_path' => $signedDocumentPath,
            'signed_document_disk' => $signedDocumentDisk,
        ], $signatureData);

        return self::create($signatureData);
    }

    public function signatureRequest(): BelongsTo
    {
        return $this->belongsTo(SignatureRequest::class);
    }
}
