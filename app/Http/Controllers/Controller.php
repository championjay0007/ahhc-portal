<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function formatFiscalQuarterLabel($date): string
    {
        $dt = Carbon::parse($date);
        $month = $dt->month;

        $quarterNumber = match (true) {
            in_array($month, [7, 8, 9], true) => 1,
            in_array($month, [10, 11, 12], true) => 2,
            in_array($month, [1, 2, 3], true) => 3,
            default => 4,
        };

        $fiscalYear = $month >= 7 ? $dt->year + 1 : $dt->year;

        return "Q{$quarterNumber} {$fiscalYear}";
    }
}
