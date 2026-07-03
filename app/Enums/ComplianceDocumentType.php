<?php

namespace App\Enums;

enum ComplianceDocumentType: string
{
    case POLICE_CHECK = 'Police Check';
    case NDIS_WORKER_SCREENING = 'NDIS Worker Screening';
    case INSURANCE = 'Insurance';
    case FIRST_AID_CERTIFICATE = 'First Aid Certificate';
    case CPR_CERTIFICATE = 'CPR Certificate';
    case QUALIFICATION = 'Qualification';
    case REGISTRATION = 'Registration';
    case ABN_VERIFICATION = 'ABN Verification';
    case MARKETPLACE_AGREEMENT = 'Marketplace Agreement';

    public static function labels(): array
    {
        return [
            self::POLICE_CHECK->value => 'Police Check',
            self::NDIS_WORKER_SCREENING->value => 'NDIS Worker Screening',
            self::INSURANCE->value => 'Insurance',
            self::FIRST_AID_CERTIFICATE->value => 'First Aid Certificate',
            self::CPR_CERTIFICATE->value => 'CPR Certificate',
            self::QUALIFICATION->value => 'Qualification',
            self::REGISTRATION->value => 'Registration',
            self::ABN_VERIFICATION->value => 'ABN Verification',
            self::MARKETPLACE_AGREEMENT->value => 'Marketplace Agreement',
        ];
    }

    public static function options(): array
    {
        return array_map(fn ($case) => ['value' => $case->value, 'label' => $case->value], self::cases());
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public function isCritical(): bool
    {
        return in_array($this, [
            self::POLICE_CHECK,
            self::NDIS_WORKER_SCREENING,
            self::INSURANCE,
        ]);
    }
}
