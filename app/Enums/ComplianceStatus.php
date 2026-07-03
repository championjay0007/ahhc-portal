<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    case ACTIVE = 'Active';
    case EXPIRING_SOON = 'Expiring Soon';
    case EXPIRED = 'Expired';
    case MISSING = 'Missing';
    case REJECTED = 'Rejected';

    public static function labels(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::EXPIRING_SOON->value => 'Expiring Soon',
            self::EXPIRED->value => 'Expired',
            self::MISSING->value => 'Missing',
            self::REJECTED->value => 'Rejected',
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

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::EXPIRING_SOON => 'warning',
            self::EXPIRED => 'danger',
            self::MISSING => 'secondary',
            self::REJECTED => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-success',
            self::EXPIRING_SOON => 'badge-warning text-dark',
            self::EXPIRED => 'badge-danger',
            self::MISSING => 'badge-secondary',
            self::REJECTED => 'badge-danger',
        };
    }
}
