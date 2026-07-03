<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case NEW_APPLICATION = 'new_application';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::NEW_APPLICATION => 'New Application',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW_APPLICATION => 'secondary',
            self::UNDER_REVIEW => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
