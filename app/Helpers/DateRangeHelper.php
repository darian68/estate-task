<?php

use Carbon\Carbon;

/**
 * Convert a client date (in any timezone) to the UTC start of that day.
 *
 * @param  string  $date      The date string (e.g. '2025-11-01')
 * @param  string  $timezone  The client's timezone (default: 'UTC')
 * @return \Carbon\Carbon     The UTC datetime at the start of the given day
 *
 * @throws \InvalidArgumentException If the date or timezone is invalid
 */
if (! function_exists('startDateToUtc')) {
    function startDateToUtc(string $date, string $timezone = 'UTC'): Carbon
    {
        try {
            $tz = new DateTimeZone($timezone);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid timezone: ' . $timezone);
        }

        try {
            $dt = new Carbon($date, $tz);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid date: ' . $date);
        }

        return $dt->startOfDay()->setTimezone('UTC');
    }
}

/**
 * Convert the end of a given client date to UTC.
 *
 * @param  string  $date      The date string (e.g. '2025-11-01')
 * @param  string  $timezone  The client's timezone (default: 'UTC')
 * @return \Carbon\Carbon     The UTC datetime at the end of the given day
 *
 * @throws \InvalidArgumentException If the date or timezone is invalid
 */
if (! function_exists('endDateToUtc')) {
    function endDateToUtc(string $date, string $timezone = 'UTC'): Carbon
    {
        try {
            $tz = new DateTimeZone($timezone);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid timezone: ' . $timezone);
        }

        try {
            $dt = new Carbon($date, $tz);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid date: ' . $date);
        }

        return $dt->endOfDay()->setTimezone('UTC');
    }
}