<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParticipantDocument extends Model
{
    use HasFactory;

    public const CATEGORY_CARE_PLAN = 'Care Plan';

    public const CATEGORY_SUPPORT_PLAN = 'Support Plan';

    public const CATEGORY_REFERRAL_DOCUMENTS = 'Referral Documents';

    public const CATEGORY_AUTHORITY_DOCUMENTS = 'Authority Documents';

    public const CATEGORY_FUNDING_DOCUMENTS = 'Funding Documents';

    public const CATEGORY_IDENTIFICATION = 'Identification';

    public const MANDATORY_CATEGORIES = [
        self::CATEGORY_CARE_PLAN,
        self::CATEGORY_SUPPORT_PLAN,
        self::CATEGORY_IDENTIFICATION,
    ];

    public const ALLOWED_FILE_EXTENSIONS = ['pdf', 'docx', 'xlsx', 'jpg', 'png'];

    public const MAX_FILE_SIZE_KB = 10240;

    protected $fillable = [
        'participant_id',
        'category',
        'title',
        'storage_disk',
        'path',
        'mime_type',
        'size_bytes',
        'uploaded_by_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size_bytes' => 'integer',
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

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ParticipantDocumentVersion::class);
    }

    public function latestVersion(): ?ParticipantDocumentVersion
    {
        return $this->versions()->orderByDesc('version_number')->first();
    }

    public function isMandatory(): bool
    {
        return in_array($this->category, self::MANDATORY_CATEGORIES, true);
    }
}
