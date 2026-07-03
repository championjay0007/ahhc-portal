<?php

namespace App\Enums;

enum ReviewType: string
{
    case STANDARD = 'Standard';
    case INTERIM = 'Interim';
    case ANNUAL = 'Annual';
    case INITIAL = 'Initial';
    case REASSESSMENT = 'Reassessment';
    case TRANSITION = 'Transition';

    public static function labels(): array
    {
        return [
            self::STANDARD->value => 'Standard Review',
            self::INTERIM->value => 'Interim Review',
            self::ANNUAL->value => 'Annual Review',
            self::INITIAL->value => 'Initial Review',
            self::REASSESSMENT->value => 'Reassessment',
            self::TRANSITION->value => 'Transition Review',
        ];
    }

    public static function options(): array
    {
        return array_map(fn ($case) => ['value' => $case->value, 'label' => self::labels()[$case->value]], self::cases());
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }
}
