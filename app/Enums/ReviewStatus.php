<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case DUE = 'Due';
    case COMPLETED = 'Completed';
    case OVERDUE = 'Overdue';
    case IN_PROGRESS = 'In Progress';
    case CANCELLED = 'Cancelled';

    public static function labels(): array
    {
        return [
            self::DUE->value => 'Due',
            self::COMPLETED->value => 'Completed',
            self::OVERDUE->value => 'Overdue',
            self::IN_PROGRESS->value => 'In Progress',
            self::CANCELLED->value => 'Cancelled',
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
            self::DUE => 'info',
            self::COMPLETED => 'success',
            self::OVERDUE => 'danger',
            self::IN_PROGRESS => 'warning',
            self::CANCELLED => 'secondary',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DUE => 'badge-info',
            self::COMPLETED => 'badge-success',
            self::OVERDUE => 'badge-danger',
            self::IN_PROGRESS => 'badge-warning text-dark',
            self::CANCELLED => 'badge-secondary',
        };
    }
}
