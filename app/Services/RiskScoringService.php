<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\ParticipantRiskScore;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;

class RiskScoringService
{
    public const LEVEL_LOW = 'Low Risk';

    public const LEVEL_MEDIUM = 'Medium Risk';

    public const LEVEL_HIGH = 'High Risk';

    public const LEVEL_CRITICAL = 'Critical Risk';

    public function recordScore(Participant $participant, ?int $userId = null): ParticipantRiskScore
    {
        $factors = $this->calculateFactors($participant);
        $score = $this->calculateScore($factors);
        $level = $this->levelForScore($score);
        $reasons = $this->buildReasons($factors);

        $latest = $participant->riskScores()->latest('calculated_at')->first();

        if ($latest && $this->isSameCalculation($latest, $score, $level, $reasons, $factors)) {
            return $latest;
        }

        $record = $participant->riskScores()->create([
            'score' => $score,
            'level' => $level,
            'trigger_reasons' => $reasons,
            'score_breakdown' => $factors,
            'calculated_by_id' => $userId ?? Auth::id(),
            'calculated_at' => now(),
        ]);

        $this->trackScoreChange($participant, $latest, $record, $userId);

        return $record;
    }

    public function calculateFactors(Participant $participant): array
    {
        $incidentCount = $participant->incidents()->count();
        $highSeverityIncidents = $participant->incidents()
            ->whereIn('severity', ['high', 'critical'])
            ->count();

        $openComplaints = $participant->complaints()
            ->where('status', 'open')
            ->count();

        $complianceComplaints = $participant->complaints()
            ->where('category', 'compliance')
            ->count();

        $missedServices = $participant->shifts()
            ->where('status', Shift::STATUS_MISSED)
            ->count();

        $overdueReviews = $participant->monthlyReviews()
            ->where('status', 'overdue')
            ->count();

        $reviewConcerns = $participant->monthlyReviews()
            ->whereNotNull('concerns')
            ->where('concerns', '<>', '')
            ->count();

        return [
            'incident_count' => $incidentCount,
            'high_severity_incidents' => $highSeverityIncidents,
            'open_complaints' => $openComplaints,
            'compliance_complaints' => $complianceComplaints,
            'missed_services' => $missedServices,
            'overdue_reviews' => $overdueReviews,
            'review_concerns' => $reviewConcerns,
        ];
    }

    public function calculateScore(array $factors): int
    {
        $score = 0;
        $score += $factors['incident_count'] * 4;
        $score += $factors['high_severity_incidents'] * 8;
        $score += $factors['open_complaints'] * 5;
        $score += $factors['compliance_complaints'] * 8;
        $score += $factors['missed_services'] * 6;
        $score += $factors['overdue_reviews'] * 10;
        $score += $factors['review_concerns'] * 4;

        return max(0, $score);
    }

    public function levelForScore(int $score): string
    {
        if ($score >= 50) {
            return self::LEVEL_CRITICAL;
        }

        if ($score >= 25) {
            return self::LEVEL_HIGH;
        }

        if ($score >= 10) {
            return self::LEVEL_MEDIUM;
        }

        return self::LEVEL_LOW;
    }

    public function buildReasons(array $factors): array
    {
        $reasons = [];

        if ($factors['incident_count'] > 0) {
            $count = $factors['incident_count'];
            $reasons[] = "Incident history: {$count} incident".($count > 1 ? 's' : '');
        }

        if ($factors['high_severity_incidents'] > 0) {
            $count = $factors['high_severity_incidents'];
            $reasons[] = 'High severity incident'.($count > 1 ? 's' : '').' recorded';
        }

        if ($factors['open_complaints'] > 0) {
            $count = $factors['open_complaints'];
            $reasons[] = 'Open complaint'.($count > 1 ? 's' : '').' require follow-up';
        }

        if ($factors['compliance_complaints'] > 0) {
            $count = $factors['compliance_complaints'];
            $reasons[] = 'Compliance-related complaint'.($count > 1 ? 's' : '').' present';
        }

        if ($factors['missed_services'] > 0) {
            $count = $factors['missed_services'];
            $reasons[] = 'Missed service'.($count > 1 ? 's' : '').' detected';
        }

        if ($factors['overdue_reviews'] > 0) {
            $count = $factors['overdue_reviews'];
            $reasons[] = 'Overdue review'.($count > 1 ? 's' : '').' pending';
        }

        if ($factors['review_concerns'] > 0) {
            $count = $factors['review_concerns'];
            $reasons[] = 'Review concern'.($count > 1 ? 's' : '').' logged';
        }

        return $reasons ?: ['No current risk triggers'];
    }

    protected function isSameCalculation(ParticipantRiskScore $latest, int $score, string $level, array $reasons, array $factors): bool
    {
        return $latest->score === $score
            && $latest->level === $level
            && $latest->trigger_reasons === $reasons
            && $latest->score_breakdown === $factors
            && $latest->calculated_at->isToday();
    }

    protected function trackScoreChange(Participant $participant, ?ParticipantRiskScore $previous, ParticipantRiskScore $current, ?int $userId = null): void
    {
        AuditLogService::record(
            'Participant risk score calculated',
            $participant,
            [
                'score' => $previous?->score,
                'level' => $previous?->level,
                'trigger_reasons' => $previous?->trigger_reasons,
            ],
            [
                'score' => $current->score,
                'level' => $current->level,
                'trigger_reasons' => $current->trigger_reasons,
            ],
            $userId
        );

        if ($previous && $this->levelWeight($current->level) > $this->levelWeight($previous->level)) {
            $this->notifyCareManagers($participant, $current, $previous);
        }
    }

    protected function levelWeight(string $level): int
    {
        return match ($level) {
            self::LEVEL_LOW => 1,
            self::LEVEL_MEDIUM => 2,
            self::LEVEL_HIGH => 3,
            self::LEVEL_CRITICAL => 4,
            default => 1,
        };
    }

    protected function notifyCareManagers(Participant $participant, ParticipantRiskScore $current, ParticipantRiskScore $previous): void
    {
        $assignedWorkers = $participant->assignments()->with('worker.user')->get();

        foreach ($assignedWorkers as $assignment) {
            $worker = $assignment->worker;
            $userId = $worker?->user_id;

            if (! $userId) {
                continue;
            }

            NotificationService::notify([
                'user_id' => $userId,
                'title' => "Participant risk increased to {$current->level}",
                'message' => sprintf(
                    '%s %s has moved from %s to %s. Current score %s',
                    $participant->first_name,
                    $participant->last_name,
                    $previous->level,
                    $current->level,
                    $current->score
                ),
                'type' => 'warning',
                'data' => [
                    'url' => route('portal.admin.participants.show', $participant->id),
                ],
            ]);
        }
    }
}
