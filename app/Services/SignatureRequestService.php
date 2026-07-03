<?php

namespace App\Services;

use App\Models\Document;
use App\Models\SignatureRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SignatureRequestService
{
    public function create(Document $document, User $signer, User $assignedBy): SignatureRequest
    {
        $signatureRequest = SignatureRequest::create([
            'document_id' => $document->id,
            'assigned_user_id' => $signer->id,
            'assigned_by' => $assignedBy->id,
            'status' => SignatureRequest::STATUS_PENDING,
            'assigned_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        NotificationService::notify([
            'user_id' => $signer->id,
            'type' => 'info',
            'data' => [
                'title' => 'Signature request assigned',
                'message' => "A new document '{$document->title}' has been assigned for your signature.",
                'url' => route('portal.participant.documents.pending'),
            ],
        ]);

        return $signatureRequest;
    }

    public function scanAndSendReminders(): array
    {
        $results = [
            'reminders_3d' => 0,
            'reminders_7d' => 0,
            'expired' => 0,
        ];

        $threeDayRequests = SignatureRequest::pending()
            ->whereNull('reminder_sent_at_3d')
            ->where('assigned_at', '<=', now()->subDays(3))
            ->get();

        foreach ($threeDayRequests as $request) {
            NotificationService::notify([
                'user_id' => $request->assigned_user_id,
                'type' => 'warning',
                'data' => [
                    'title' => 'Signature reminder',
                    'message' => "Reminder: '{$request->document->title}' is due for your signature.",
                    'url' => route('portal.participant.documents.pending'),
                ],
            ]);

            $request->update(['reminder_sent_at_3d' => now()]);
            $results['reminders_3d']++;
        }

        $sevenDayRequests = SignatureRequest::pending()
            ->whereNull('reminder_sent_at_7d')
            ->where('assigned_at', '<=', now()->subDays(7))
            ->get();

        foreach ($sevenDayRequests as $request) {
            NotificationService::notify([
                'user_id' => $request->assigned_user_id,
                'type' => 'warning',
                'data' => [
                    'title' => 'Second signature reminder',
                    'message' => "Second reminder: '{$request->document->title}' still needs your signature.",
                    'url' => route('portal.participant.documents.pending'),
                ],
            ]);

            $request->update(['reminder_sent_at_7d' => now()]);
            $results['reminders_7d']++;
        }

        $expiredRequests = SignatureRequest::pending()
            ->where('assigned_at', '<=', now()->subDays(30))
            ->get();

        foreach ($expiredRequests as $request) {
            $request->markExpired();

            NotificationService::notify([
                'user_id' => $request->assigned_by,
                'type' => 'danger',
                'data' => [
                    'title' => 'Signature request expired',
                    'message' => "Signature request for '{$request->document->title}' has expired.",
                    'url' => route('portal.admin.documents.show', $request->document),
                ],
            ]);

            $results['expired']++;
        }

        Log::info('Signature request reminder scan completed', $results);

        return $results;
    }
}
