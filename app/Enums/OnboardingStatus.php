<?php

namespace App\Enums;

enum OnboardingStatus: string
{
    case INVITATION_SENT = 'invitation_sent';
    case IN_PROGRESS = 'in_progress';
    case PENDING_REVIEW = 'pending_review';
    case CHANGES_REQUESTED = 'changes_requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ACTIVATED = 'activated';

    public function label(): string
    {
        return match ($this) {
            self::INVITATION_SENT => 'Invitation Sent',
            self::IN_PROGRESS => 'In Progress',
            self::PENDING_REVIEW => 'Pending Review',
            self::CHANGES_REQUESTED => 'Changes Requested',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::ACTIVATED => 'Activated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INVITATION_SENT => 'info',
            self::IN_PROGRESS => 'primary',
            self::PENDING_REVIEW => 'warning',
            self::CHANGES_REQUESTED => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::ACTIVATED => 'success',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::INVITATION_SENT => '📧 Invitation Sent',
            self::IN_PROGRESS => '⏳ In Progress',
            self::PENDING_REVIEW => '👀 Pending Review',
            self::CHANGES_REQUESTED => '✏️ Changes Requested',
            self::APPROVED => '✅ Approved',
            self::REJECTED => '❌ Rejected',
            self::ACTIVATED => '🎉 Activated',
        };
    }
}
