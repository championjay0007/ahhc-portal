<?php

namespace App\Support;

use App\Models\PortalSetting;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

final class DateTimeDisplay
{
    public static function format(mixed $value, string $format, ?string $timezone = null): ?string
    {
        $dateTime = self::asCarbon($value);

        return $dateTime?->setTimezone(self::timezone($timezone))->format($format);
    }

    public static function relative(mixed $value, ?string $timezone = null): ?string
    {
        $dateTime = self::asCarbon($value);

        return $dateTime?->setTimezone(self::timezone($timezone))->diffForHumans();
    }

    public static function timezone(?string $timezone = null): string
    {
        if (is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true)) {
            return $timezone;
        }

        $userTimezone = Auth::user()?->timezone;
        if (is_string($userTimezone) && in_array($userTimezone, timezone_identifiers_list(), true)) {
            return $userTimezone;
        }

        try {
            $defaultTimezone = PortalSetting::query()->where('key', 'default_timezone')->value('value');
            if (is_string($defaultTimezone) && in_array($defaultTimezone, timezone_identifiers_list(), true)) {
                return $defaultTimezone;
            }
        } catch (QueryException $exception) {
            report($exception);
        }

        $applicationTimezone = config('app.timezone', 'UTC');

        return is_string($applicationTimezone) && in_array($applicationTimezone, timezone_identifiers_list(), true)
            ? $applicationTimezone
            : 'UTC';
    }

    private static function asCarbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value)) {
            return Carbon::parse($value, 'UTC');
        }

        throw new InvalidArgumentException('Date/time display value must be a date-time object, string, or null.');
    }

}
