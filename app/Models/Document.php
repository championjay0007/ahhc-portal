<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Document extends Model
{
    public const ALLOWED_FILE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'csv'];

    public const MAX_FILE_SIZE_KB = 10240;

    public const MAX_FILE_SIZE_BYTES = self::MAX_FILE_SIZE_KB * 1024;

    public const PARTICIPANT_DOCUMENT_CATEGORIES = [
        'Care Plan',
        'Support Plan',
        'Referral Documents',
        'Authority Documents',
        'Funding Documents',
        'Identification',
        'Other',
    ];

    public const MANDATORY_PARTICIPANT_DOCUMENT_CATEGORIES = [
        'Care Plan',
        'Support Plan',
        'Identification',
    ];

    protected $fillable = [
        'owner_type',
        'owner_id',
        'document_type',
        'description',
        'title',
        'storage_disk',
        'path',
        'mime_type',
        'size_bytes',
        'uploaded_by_id',
        'status',
        'onboarding_required',
        'expires_at',
        'is_sensitive',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_sensitive' => 'boolean',
        'metadata' => 'array',
        'size_bytes' => 'integer',
        'onboarding_required' => 'boolean',
    ];

    public static function fileValidationRules(): array
    {
        return [
            'required',
            'file',
            'mimes:'.implode(',', self::ALLOWED_FILE_EXTENSIONS),
            'max:'.self::MAX_FILE_SIZE_KB,
        ];
    }

    public static function participantDocumentCategories(): array
    {
        return self::PARTICIPANT_DOCUMENT_CATEGORIES;
    }

    public static function mandatoryParticipantDocumentCategories(): array
    {
        return self::MANDATORY_PARTICIPANT_DOCUMENT_CATEGORIES;
    }

    public static function participantDocumentCategoryOptions(): array
    {
        return array_combine(self::PARTICIPANT_DOCUMENT_CATEGORIES, self::PARTICIPANT_DOCUMENT_CATEGORIES);
    }

    public static function normalizeParticipantDocumentCategory(string $category): string
    {
        // Accept either the display label (e.g. "Care Plan") or a snake_case
        // key (e.g. "care_plan"). If the input matches a known display label
        // (case/spacing-insensitive) return the canonical snake_case key.
        $normalizedInput = strtolower(str_replace([' ', '_'], '', $category));

        foreach (self::PARTICIPANT_DOCUMENT_CATEGORIES as $label) {
            if (strtolower(str_replace([' ', '_'], '', $label)) === $normalizedInput) {
                return Str::snake($label);
            }
        }

        // If no matching display label, assume the provided value is already
        // a canonical key (e.g. "care_plan") and return it unchanged.
        return $category;
    }

    public static function denormalizeParticipantDocumentCategory(string $category): string
    {
        // Convert snake_case (e.g. "care_plan") back to display label (e.g. "Care Plan")
        $normalizedInput = strtolower(str_replace([' ', '_'], '', $category));

        foreach (self::PARTICIPANT_DOCUMENT_CATEGORIES as $label) {
            if (strtolower(str_replace([' ', '_'], '', $label)) === $normalizedInput) {
                return $label;
            }
        }

        // If no match found, return the original value
        return $category;
    }

    public function isMandatoryParticipantDocument(): bool
    {
        return in_array(self::normalizeParticipantDocumentCategory($this->document_type), self::MANDATORY_PARTICIPANT_DOCUMENT_CATEGORIES, true);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    public function signatureRequests(): HasMany
    {
        return $this->hasMany(SignatureRequest::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany('version_number');
    }

    public static function isPreviewableMime(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf';
    }

    public function isPreviewable(): bool
    {
        return $this->mime_type && self::isPreviewableMime($this->mime_type);
    }

    public function getOwnerLabelAttribute(): string
    {
        if (! $this->owner) {
            return 'Unknown';
        }

        return match (get_class($this->owner)) {
            Participant::class, Worker::class => trim($this->owner->first_name.' '.$this->owner->last_name),
            Invoice::class => 'Invoice #'.$this->owner->invoice_number,
            Incident::class => 'Incident #'.$this->owner->id,
            PreApprovalRequest::class => 'Pre-approval #'.$this->owner->id,
            MonthlyCareReview::class => 'Care review #'.$this->owner->id,
            default => class_basename($this->owner).' #'.$this->owner->id,
        };
    }

    public function getHasExpiryAttribute(): bool
    {
        return ! is_null($this->expires_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function addVersionFromUploadedFile(UploadedFile $file, ?User $uploadedBy = null, ?string $notes = null): DocumentVersion
    {
        $path = $file->store('documents', 'local');
        $versionNumber = ($this->versions()->max('version_number') ?? 0) + 1;

        $version = $this->versions()->create([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $uploadedBy?->id,
            'version_number' => $versionNumber,
            'notes' => $notes,
        ]);

        $this->update([
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $uploadedBy?->id,
            'status' => 'uploaded',
        ]);

        return $version;
    }
}
