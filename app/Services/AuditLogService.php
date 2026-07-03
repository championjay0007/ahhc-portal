<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function record(
        string $action,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null
    ): AuditLog {
        $request = request();
        $userAgent = $request?->userAgent();
        $userAgentMeta = self::parseUserAgent($userAgent);

        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'model_type' => $subject?->getMorphClass(),
            'model_id' => $subject?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'changes' => [
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $userAgent,
            'browser' => $userAgentMeta['browser'],
            'device' => $userAgentMeta['device'],
        ]);
    }

    private static function parseUserAgent(?string $userAgent): array
    {
        if (! $userAgent) {
            return ['browser' => 'Unknown', 'device' => 'Unknown'];
        }

        $browser = 'Unknown';
        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari') && ! str_contains($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR/')) {
            $browser = 'Opera';
        }

        $device = 'Desktop';
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android')) {
            $device = 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            $device = 'Tablet';
        }

        return ['browser' => $browser, 'device' => $device];
    }
}
