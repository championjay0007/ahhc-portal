<?php

namespace App\Enums;

enum WorkerDeclarationType: string
{
    case PRIVACY = 'privacy';
    case INCIDENT_REPORTING = 'incident_reporting';
    case CARE_NOTES = 'care_notes';
    case NO_COMMENCEMENT = 'no_commencement';
    case CODE_OF_CONDUCT = 'code_of_conduct';
    case THIRD_PARTY = 'third_party';

    public function label(): string
    {
        return match ($this) {
            self::PRIVACY => 'Privacy Agreement',
            self::INCIDENT_REPORTING => 'Incident Reporting',
            self::CARE_NOTES => 'Care Notes Requirement',
            self::NO_COMMENCEMENT => 'No Commencement Before Approval',
            self::CODE_OF_CONDUCT => 'Code of Conduct',
            self::THIRD_PARTY => 'Third-Party Agreements',
        };
    }

    public function defaultText(): string
    {
        return match ($this) {
            self::PRIVACY => 'I agree to maintain confidentiality of all participant information and comply with privacy regulations.',
            self::INCIDENT_REPORTING => 'I agree to report all incidents and safety concerns immediately to AHHC.',
            self::CARE_NOTES => 'I understand that care notes are mandatory for all service delivery and must be submitted promptly.',
            self::NO_COMMENCEMENT => 'I understand that I must not commence work with any participant until formal approval has been provided by AHHC.',
            self::CODE_OF_CONDUCT => 'I agree to adhere to AHHC code of conduct and professional standards.',
            self::THIRD_PARTY => 'I agree to comply with all third-party service agreements and marketplace terms.',
        };
    }

    public static function all(): array
    {
        return self::cases();
    }
}
