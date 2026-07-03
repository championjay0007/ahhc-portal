<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Signature Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 24px; }
        h1 { margin-bottom: 16px; }
        .section { margin-bottom: 18px; }
        .label { font-weight: bold; }
        .certificate { border: 2px solid #333; padding: 18px; border-radius: 8px; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Signature Certificate</h1>

    <div class="certificate">
        <div class="section">
            <p class="label">Agreement:</p>
            <p>{{ $agreementName }}</p>
        </div>

        <div class="section">
            <p class="label">Signed by:</p>
            <p>{{ $signedByName }}</p>
        </div>

        <div class="section">
            <p class="label">Signed at:</p>
            <p>{{ $signedAt->format('Y-m-d H:i:s') }}</p>
        </div>

        <div class="section">
            <p class="label">IP Address:</p>
            <p>{{ $ipAddress }}</p>
        </div>

        <div class="section">
            <p class="label">User agent:</p>
            <p>{{ $userAgent }}</p>
        </div>

        <div class="section">
            <p class="label">Signature certificate hash:</p>
            <p>{{ $signatureHash }}</p>
        </div>
    </div>

    <div class="section">
        <p>This certificate confirms the captured signature metadata for the above agreement.</p>
    </div>
</body>
</html>
