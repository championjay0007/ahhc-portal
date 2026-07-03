<?php

$vendor = __DIR__.'/../vendor/autoload.php';
if (! file_exists($vendor)) {
    echo "vendor autoload not found, run composer install\n";
    exit(1);
}
require $vendor;
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\WorkerNomination;
use Illuminate\Contracts\Console\Kernel;

$nominations = WorkerNomination::with(['participant'])->get();
echo 'Total nominations: '.$nominations->count().PHP_EOL;
foreach ($nominations as $nom) {
    echo sprintf("%d | %s | %s | participant_id=%s\n", $nom->id, $nom->worker_full_name, $nom->status->value, $nom->participant_id);
}
