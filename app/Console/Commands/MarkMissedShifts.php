<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkMissedShifts extends Command
{
    protected $signature = 'shifts:mark-missed';

    protected $description = 'Mark shifts as missed if they have passed without being completed';

    public function handle(): int
    {
        $yesterday = Carbon::now()->subDay()->endOfDay();

        // Find shifts that were scheduled/confirmed but not completed/cancelled/missed
        $missedShifts = Shift::where('shift_date', '<', Carbon::now()->format('Y-m-d'))
            ->whereIn('status', [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED, Shift::STATUS_IN_PROGRESS])
            ->get();

        $count = 0;

        foreach ($missedShifts as $shift) {
            $shift->update(['status' => Shift::STATUS_MISSED]);
            AuditLogService::record('Shift Marked as Missed', $shift, 
                ['status' => $shift->getOriginal('status')], 
                ['status' => Shift::STATUS_MISSED]);
            $count++;
        }

        $this->info("Marked {$count} shifts as missed.");

        return Command::SUCCESS;
    }
}
