<?php

namespace App\Console;

use App\Console\Commands\ExpirePreApprovals;
use App\Jobs\ScanCareReviews;
use App\Jobs\ScanWorkerCompliance;
use App\Jobs\SendShiftReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        ExpirePreApprovals::class,
        \App\Console\Commands\NormalizeBudgets::class,
        \App\Console\Commands\MigrateMessageTemplatesToEmailTemplates::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily compliance scan at 3 AM
        $schedule->job(new ScanWorkerCompliance)
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Daily care review scan and notifications at 8 AM
        $schedule->job(new ScanCareReviews)
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Daily shift reminder scan at 07:00
        $schedule->job(new SendShiftReminders)
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Daily expiry check for approved pre-approvals
        $schedule->command(ExpirePreApprovals::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();
    }
}
