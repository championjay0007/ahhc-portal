<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerComplianceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_required',
        'is_critical',
        'default_status',
    ];

    protected $casts = [
        'is_required' => 'bool',
        'is_critical' => 'bool',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(WorkerComplianceDocument::class);
    }

    public static function defaultTypes(): array
    {
        return [
            [
                'name' => 'Police Check',
                'slug' => 'police_check',
                'is_required' => true,
                'is_critical' => true,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'NDIS Worker Screening',
                'slug' => 'ndis_worker_screening',
                'is_required' => false,
                'is_critical' => true,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'Insurance',
                'slug' => 'insurance',
                'is_required' => false,
                'is_critical' => true,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'First Aid Certificate',
                'slug' => 'first_aid_certificate',
                'is_required' => false,
                'is_critical' => false,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'CPR Certificate',
                'slug' => 'cpr_certificate',
                'is_required' => false,
                'is_critical' => false,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'Qualification',
                'slug' => 'qualification',
                'is_required' => false,
                'is_critical' => false,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'Registration',
                'slug' => 'registration',
                'is_required' => false,
                'is_critical' => false,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'ABN Verification',
                'slug' => 'abn_verification',
                'is_required' => false,
                'is_critical' => false,
                'default_status' => 'Missing',
            ],
            [
                'name' => 'Marketplace Agreement',
                'slug' => 'marketplace_agreement',
                'is_required' => false,
                'is_critical' => false,
                'default_status' => 'Missing',
            ],
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (self::defaultTypes() as $type) {
            self::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }

    public static function options(): array
    {
        return self::orderBy('name')->get()->map(function (self $type) {
            return [
                'value' => $type->name,
                'label' => $type->name,
                'is_critical' => $type->is_critical,
                'is_required' => $type->is_required,
            ];
        })->toArray();
    }
}
