<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingAgreementService
{
    public static function requiredAgreements(): array
    {
        return [
            'agreement_self_management' => 'Self-Management Agreement',
            'agreement_privacy' => 'Privacy Consent',
            'agreement_responsibilities' => 'Responsibilities Agreement',
            'agreement_terms' => 'Terms & Conditions',
        ];
    }

    public function createSignedAgreement(
        Participant $participant,
        string $agreementKey,
        string $fullName,
        string $signatureImage,
        string $ipAddress,
        string $userAgent
    ): DocumentSignature {
        $agreementName = self::requiredAgreements()[$agreementKey] ?? Str::headline($agreementKey);
        $fileName = Str::slug($agreementName).'_signed_'.time().'_'.uniqid();
        $signedDocumentPath = "documents/onboarding/{$fileName}.pdf";
        $certificatePath = "documents/onboarding/certificates/{$fileName}_certificate.pdf";
        $signaturePath = "documents/onboarding/signatures/{$fileName}.png";

        $pdf = Pdf::loadView('pdfs.onboarding-agreement', [
            'participant' => $participant,
            'agreementName' => $agreementName,
            'signedByName' => $fullName,
            'signedAt' => now(),
            'ipAddress' => $ipAddress,
            'userAgent' => $userAgent,
            'signatureImage' => $signatureImage,
        ]);

        Storage::disk('local')->put($signedDocumentPath, $pdf->output());

        if ($this->isSignatureImageData($signatureImage)) {
            [$meta, $encoded] = explode(',', $signatureImage, 2);
            $decoded = base64_decode($encoded);
            if ($decoded !== false) {
                Storage::disk('local')->put($signaturePath, $decoded);
            }
        }

        $certificatePdf = Pdf::loadView('pdfs.onboarding-signature-certificate', [
            'participant' => $participant,
            'agreementName' => $agreementName,
            'signedByName' => $fullName,
            'signedAt' => now(),
            'ipAddress' => $ipAddress,
            'userAgent' => $userAgent,
            'signatureHash' => hash('sha256', $participant->id.$agreementKey.$ipAddress.now()->timestamp),
        ]);

        Storage::disk('local')->put($certificatePath, $certificatePdf->output());

        $document = Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => $agreementName,
            'title' => "Signed {$agreementName}",
            'storage_disk' => 'local',
            'path' => $signedDocumentPath,
            'mime_type' => 'application/pdf',
            'size_bytes' => Storage::disk('local')->size($signedDocumentPath),
            'uploaded_by_id' => $participant->user_id,
            'status' => 'signed',
            'metadata' => [
                'agreement_key' => $agreementKey,
                'agreement_name' => $agreementName,
                'certificate_path' => $certificatePath,
            ],
        ]);

        return DocumentSignature::create([
            'document_id' => $document->id,
            'signed_by_type' => get_class($participant->user),
            'signed_by_id' => $participant->user->id,
            'signature_method' => 'electronic',
            'signed_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'signature_hash' => hash('sha256', $document->id.$participant->user->id.$ipAddress.now()->timestamp),
            'signature_path' => $signaturePath,
            'signature_disk' => 'local',
            'signed_document_path' => $signedDocumentPath,
            'signed_document_disk' => 'local',
            'certificate_path' => $certificatePath,
            'certificate_disk' => 'local',
        ]);
    }

    protected function isSignatureImageData(string $value): bool
    {
        return str_starts_with($value, 'data:image/');
    }
}
