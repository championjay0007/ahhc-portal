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

// Migrate fresh using in-memory sqlite maybe not possible from here because using current DB? We'll just use the app DB
Artisan::call('migrate:fresh', ['--force' => true]);

$user = User::create([
    'name' => 'Participant User',
    'email' => 'participant@example.com',
    'role' => 'participant',
    'status' => 'active',
    'mfa_enabled' => false,
    'password' => Hash::make('Password123!'),
    'password_changed_at' => now(),
]);

$participant = Participant::create([
    'user_id' => $user->id,
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

// Force authentication
Auth::login($admin);

$controller = new WorkerNominationController;
$request = Request::create('/portal/admin/nominations/'.$nomination->id.'/approve', 'POST');
$request->setUserResolver(fn () => $admin);

try {
    $response = $controller->approve($nomination, $request);
    echo 'Controller response: ';
    var_export($response);
    echo PHP_EOL;
} catch (Throwable $e) {
    echo 'Exception: '.get_class($e).': '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}

echo "Nomination status: {$nomination->refresh()->status}\n";
$worker = Worker::where('email', 'julia.worker@example.com')->first();
if ($worker) {
    echo "Worker created: {$worker->id} / {$worker->email}\n";
} else {
    echo "Worker not created\n";
}
