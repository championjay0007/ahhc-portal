<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDocument extends Model
{
    protected $fillable = [
        'assessment_id',
        'uploaded_by_user_id',
        'document_category',
        'document_type',
        'document_name',
        'file_name',
        'storage_disk',
        'storage_path',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
        'rejected_at',
        'rejected_by_user_id',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'rejected_at' => 'datetime',
        'metadata' => 'array',
    ];

    const CATEGORY_REFERRAL = 'referral';

    const CATEGORY_CARE_PLAN = 'care_plan';

    const CATEGORY_SUPPORT_PLAN = 'support_plan';

    const CATEGORY_AUTHORITY = 'authority';

    const CATEGORY_FUNDING = 'funding';

    const CATEGORY_PARTICIPANT = 'participant_doc';

    const STATUS_RECEIVED = 'received';

    const STATUS_PENDING = 'pending';

    const STATUS_MISSING = 'missing';

    const STATUS_REJECTED = 'rejected';

    const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
    ];

    const MAX_FILE_SIZE = 10485760; // 10MB in bytes

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    /**
     * Get category label
     */
    public static function getCategoryLabel(string $category): string
    {
        $labels = [
            self::CATEGORY_REFERRAL => 'Referral Documents',
            self::CATEGORY_CARE_PLAN => 'Care Plan',
            self::CATEGORY_SUPPORT_PLAN => 'Support Plan',
            self::CATEGORY_AUTHORITY => 'Authority Documents',
            self::CATEGORY_FUNDING => 'Funding Documents',
            self::CATEGORY_PARTICIPANT => 'Participant Documents',
        ];

        return $labels[$category] ?? $category;
    }

    /**
     * Get status label
     */
    public static function getStatusLabel(string $status): string
    {
        $labels = [
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_MISSING => 'Missing',
            self::STATUS_REJECTED => 'Rejected',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Check file validation
     */
    public static function validateFile($file): array
    {
        $errors = [];

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds 10MB limit';
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            $errors[] = 'File type not allowed. Accepted types: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, CSV';
        }

        return $errors;
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('document_category', $category);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
