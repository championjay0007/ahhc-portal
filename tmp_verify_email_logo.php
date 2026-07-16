<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logo = App\Models\PortalSetting::where('key', 'logo_path')->value('value');
$logoPath = $logo ?: 'NULL';
$publicUrl = $logo ? asset('storage/' . ltrim($logo, '/')) : 'NULL';

echo "LOGO_PATH: $logoPath\n";
echo "PUBLIC_URL: $publicUrl\n";

$mail = new App\Mail\StyledEmail(
    'Test subject',
    'Test headline',
    'Test subtitle',
    'Test intro'
);
$html = $mail->render();
$present = $logo && strpos($html, $publicUrl) !== false ? 'YES' : 'NO';

echo "MAIL_HAS_LOGO_URL: $present\n";
echo "HTML_SNIPPET:\n" . substr($html, 0, 700) . "\n";
