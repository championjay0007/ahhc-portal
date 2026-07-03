<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Enums\WorkerNominationStatus;
use App\Http\Controllers\WorkerNominationController;
use App\Models\Participant;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerNomination;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Force refresh database for this script if using testing environment.
// This script may run against disk DB, so beware.

Artisan::call('migrate:fresh', ['--force' => true]);

$participantUser = User::create([
    'name' => 'Participant User',
    'email' => 'participant@example.com',
    'role' => 'participant',
    'status' => 'active',
    'mfa_enabled' => false,
    'password' => Hash::make('Password123!'),
    'password_changed_at' => now(),
]);

$participant = Participant::create([
    'user_id' => $participantUser->id,
    'participant_number' => 'P-1001',
    'first_name' => 'Participant',
    'last_name' => 'Example',
    'status' => 'active',
    'phone' => '0400111222',
    'email' => 'participant@example.com',
]);

$nomination = WorkerNomination::create([
    'participant_id' => $participant->id,
    'worker_full_name' => 'Julia Worker',
    'worker_email' => 'julia.worker@example.com',
    'worker_phone' => '0411222333',
    'worker_type' => 'Independent',
    'service_type' => 'Personal Care',
    'status' => WorkerNominationStatus::Submitted,
]);

$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'role' => 'admin',
    'status' => 'active',
    'mfa_enabled' => false,
    'password' => Hash::make('Password123!'),
    'password_changed_at' => now(),
]);

Auth::login($admin);

$request = Request::create('/portal/admin/nominations/'.$nomination->id.'/approve', 'POST');
$request->setUserResolver(fn () => $admin);

$controller = new WorkerNominationController;

try {
    $response = $controller->approve($nomination, $request);
    echo 'Response class: '.get_class($response).PHP_EOL;
    if (method_exists($response, 'getStatusCode')) {
        echo 'Status code: '.$response->getStatusCode().PHP_EOL;
    }
    if (method_exists($response, 'getTargetUrl')) {
        echo 'Target URL: '.$response->getTargetUrl().PHP_EOL;
    }
    echo 'Nomination status after approve: '.$nomination->refresh()->status.PHP_EOL;
    $worker = Worker::where('email', 'julia.worker@example.com')->first();
    echo 'Worker record exists: '.($worker ? 'yes ('.$worker->id.')' : 'no').PHP_EOL;
    if ($worker) {
        echo 'Worker onboarding token: '.$worker->onboarding_token.PHP_EOL;
        echo 'Worker status: '.$worker->status.PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'Exception: '.get_class($e).': '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString();
}
