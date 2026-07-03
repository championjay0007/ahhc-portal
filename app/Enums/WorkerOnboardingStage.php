<?php

namespace App\Enums;

enum WorkerOnboardingStage: int
{
    case STAGE_1_INVITED = 1;
    case STAGE_2_COMPLIANCE = 2;
    case STAGE_3_REVIEW = 3;
    case STAGE_4_DECLARATIONS = 4;
    case STAGE_5_SERVICES = 5;
    case STAGE_6_ASSIGNED = 6;

    public function label(): string
    {
        return match ($this) {
            self::STAGE_1_INVITED => 'Invited',
            self::STAGE_2_COMPLIANCE => 'Upload Compliance',
            self::STAGE_3_REVIEW => 'Document Review',
            self::STAGE_4_DECLARATIONS => 'Sign Declarations',
            self::STAGE_5_SERVICES => 'Service Approval',
            self::STAGE_6_ASSIGNED => 'Assigned to Participant',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::STAGE_1_INVITED => 'Account created, awaiting MFA setup',
            self::STAGE_2_COMPLIANCE => 'Upload required compliance documents',
            self::STAGE_3_REVIEW => 'AHHC reviewing compliance documents',
            self::STAGE_4_DECLARATIONS => 'Sign required declarations',
            self::STAGE_5_SERVICES => 'AHHC defining approved service categories',
            self::STAGE_6_ASSIGNED => 'Assigned to participant(s) with full access',
        };
    }

    public function isComplete(): bool
    {
        return $this === self::STAGE_6_ASSIGNED;
    }

    public static function all(): array
    {
        return self::cases();
    }
}
