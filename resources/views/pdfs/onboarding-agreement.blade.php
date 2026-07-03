<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $agreementName }} - Signed Agreement</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 24px; }
        h1, h2 { margin: 0 0 12px; }
        p { margin: 8px 0; }
        .section { margin-bottom: 18px; }
        .label { font-weight: bold; }
        .signature-box { border: 1px solid #bbb; padding: 12px; margin-top: 16px; }
        .signature-image { max-width: 100%; height: auto; border: 1px solid #ddd; }
        .footer { margin-top: 30px; font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <h1>{{ $agreementName }}</h1>
    <div class="section">
        <p>This document records the participant's acceptance of the {{ $agreementName }} for Allegiance Heart &amp; Home Care Self-Management Support.</p>
    </div>

    <div class="section">
        <p><span class="label">Signed by:</span> {{ $signedByName }}</p>
        <p><span class="label">Date:</span> {{ $signedAt->format('Y-m-d') }}</p>
        <p><span class="label">Time:</span> {{ $signedAt->format('H:i:s') }}</p>
        <p><span class="label">IP Address:</span> {{ $ipAddress }}</p>
        <p><span class="label">User agent:</span> {{ $userAgent }}</p>
    </div>

    <div class="section signature-box">
        <p class="label">Electronic signature</p>
        @if($signatureImage)
            <img class="signature-image" src="{{ $signatureImage }}" alt="Signature">
        @else
            <p>No signature image provided.</p>
        @endif
    </div>

    <div class="footer">
        <p>Generated on {{ $signedAt->format('Y-m-d H:i:s') }} by Allegiance Heart &amp; Home Care Portal.</p>
    </div>
</body>
</html>
