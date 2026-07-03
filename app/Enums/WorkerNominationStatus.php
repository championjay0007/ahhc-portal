<?php

namespace App\Enums;

enum WorkerNominationStatus: string
{
    case Submitted = 'Submitted';
    case UnderReview = 'Under Review';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case WorkerInvited = 'Worker Invited';
    case CompliancePending = 'Compliance Pending';
    case PendingSignature = 'Pending Signature';
    case Active = 'Active';
    case Assigned = 'Assigned';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::WorkerInvited => 'Worker Invited',
            self::CompliancePending => 'Compliance Pending',
            self::PendingSignature => 'Pending Signature',
            self::Active => 'Active',
            self::Assigned => 'Assigned',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Submitted => 'primary',
            self::UnderReview => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::WorkerInvited => 'warning',
            self::CompliancePending => 'warning',
            self::PendingSignature => 'info',
            self::Active => 'success',
            self::Assigned => 'success',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Rejected,
            self::Active,
            self::Assigned,
        ]);
    }

    public function canTransitionTo(WorkerNominationStatus $target): bool
    {
        $allowedTransitions = match ($this) {
            self::Submitted => [self::UnderReview, self::Approved, self::Rejected],
            self::UnderReview => [self::Approved, self::Rejected],
            self::Approved => [self::WorkerInvited, self::Rejected],
            self::Rejected => [],
            self::WorkerInvited => [self::CompliancePending],
            self::CompliancePending => [self::PendingSignature, self::Rejected],
            self::PendingSignature => [self::Active],
            self::Active => [self::Assigned],
            self::Assigned => [],
        };

        return in_array($target, $allowedTransitions);
    }
}
