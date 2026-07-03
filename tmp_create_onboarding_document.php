<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Document;
use App\Models\Participant;
use Illuminate\Contracts\Console\Kernel;

$token = 'c3a7ff52-b6bc-4289-978b-e30f0a8c3914';
$participant = Participant::where('onboarding_token', $token)->first();
if (! $participant) {
    echo "participant not found\n";
    exit(1);
}

$source = __DIR__.'/storage/app/tmp/test-agreement.pdf';
$dest = __DIR__.'/storage/app/documents/test-agreement.pdf';
if (! file_exists($source)) {
    echo "source missing: $source\n";
    exit(1);
}

copy($source, $dest);
$size = filesize($dest);

$document = Document::create([
    'owner_type' => Participant::class,
    'owner_id' => $participant->id,
    'document_type' => 'agreement',
    'description' => 'Test onboarding agreement',
    'title' => 'Self-Management Agreement',
    'storage_disk' => 'local',
    'path' => 'tmp/test-agreement.pdf',
    'mime_type' => 'application/pdf',
    'size_bytes' => $size,
    'uploaded_by_id' => null,
    'status' => 'active',
    'onboarding_required' => true,
    'expires_at' => null,
    'is_sensitive' => true,
    'metadata' => [],
]);

echo "created: {$document->id}\n";
echo "title: {$document->title}\n";
echo "token: {$token}\n";
echo "owner: {$document->owner_type} #{$document->owner_id}\n";
echo "status: {$document->status}\n";
echo "onboarding_required: {$document->onboarding_required}\n";
