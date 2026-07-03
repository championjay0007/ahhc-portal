<?php

namespace App\Enums;

enum ReportType: string
{
    case PARTICIPANT_BUDGET = 'Participant Budget';
    case INVOICE = 'Invoice';
    case CARE_NOTES = 'Care Notes';
    case INCIDENT = 'Incident';
    case INCIDENT_COMPLAINT = 'Incident / Complaint';
    case COMPLIANCE = 'Compliance';
    case PRE_APPROVAL = 'Pre-Approval';
    case CARE_REVIEW = 'Care Review';
    case AUDIT_LOG = 'Audit Log';

    public static function options(): array
    {
        return [
            self::PARTICIPANT_BUDGET->value => 'Participant Budget Report',
            self::INVOICE->value => 'Invoice Report',
            self::CARE_NOTES->value => 'Care Notes Report',
            self::INCIDENT->value => 'Incident Report',
            self::INCIDENT_COMPLAINT->value => 'Incident / Complaint Report',
            self::COMPLIANCE->value => 'Worker Compliance Report',
            self::PRE_APPROVAL->value => 'Pre-Approval Report',
            self::CARE_REVIEW->value => 'Monthly Care Management Report',
            self::AUDIT_LOG->value => 'Audit Log Export',
        ];
    }

    public static function labels(): array
    {
        return self::options();
    }
}
