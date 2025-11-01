<?php

use Carbon\Carbon;

/**
 * Convert client-side date to start of day in UTC timezone.
 *
 * @param string      $date
 * @param string|null $clientTz  If null, assumed to already be UTC
 */
if (! function_exists('startDateToUtc')) {
    function startDateToUtc(string $date, ?string $clientTz = null): Carbon
    {
        $tz = $clientTz ?? 'UTC';

        return Carbon::parse($date, $tz)
            ->startOfDay()
            ->setTimezone('UTC');
    }
}

/**
 * Convert client-side date to end of day in UTC timezone.
 *
 * @param string      $date
 * @param string|null $clientTz  If null, assumed to already be UTC
 */
if (! function_exists('endDateToUtc')) {
    function endDateToUtc(string $date, ?string $clientTz = null): Carbon
    {
        $tz = $clientTz ?? 'UTC';

        return Carbon::parse($date, $tz)
            ->endOfDay()
            ->setTimezone('UTC');
    }
}