<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Participant;
use Illuminate\Contracts\Console\Kernel;

$token = 'c3a7ff52-b6bc-4289-978b-e30f0a8c3914';
$p = Participant::where('onboarding_token', $token)->first();
if (! $p) {
    echo "participant not found\n";
    exit(1);
}
$p->status = 'onboarding';
$p->onboarding_expires_at = now()->addDays(2);
$p->onboarding_token = $token;
$p->save();

echo "participant updated: {$p->id}\n";
