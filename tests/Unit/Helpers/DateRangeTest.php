<?php

namespace Tests\Unit\Helpers;

use InvalidArgumentException;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    /** @test */
    public function it_converts_start_of_day_from_client_tz_to_utc()
    {
        // Example: 2025-11-01 00:00:00 in Asia/Ho_Chi_Minh (+7)
        $clientDate = '2025-11-01';
        $clientTz = 'Asia/Ho_Chi_Minh';

        $utc = startDateToUtc($clientDate, $clientTz);

        // Expect UTC = previous day 17:00:00 (since +7 hours)
        $this->assertEquals(
            '2025-10-31 17:00:00',
            $utc->format('Y-m-d H:i:s')
        );
        $this->assertEquals('UTC', $utc->getTimezone()->getName());
    }

    /** @test */
    public function it_converts_end_of_day_from_client_tz_to_utc()
    {
        $clientDate = '2025-11-01';
        $clientTz = 'Asia/Ho_Chi_Minh';

        $utc = endDateToUtc($clientDate, $clientTz);

        // Expect UTC = same day 16:59:59 (+7 offset)
        $this->assertEquals(
            '2025-11-01 16:59:59',
            $utc->format('Y-m-d H:i:s')
        );
        $this->assertEquals('UTC', $utc->getTimezone()->getName());
    }

    /** @test */
    public function it_returns_same_day_when_client_tz_is_already_utc()
    {
        $clientDate = '2025-11-01';

        $start = startDateToUtc($clientDate, 'UTC');
        $end = endDateToUtc($clientDate, 'UTC');

        $this->assertEquals('2025-11-01 00:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-11-01 23:59:59', $end->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_assumes_utc_when_no_timezone_provided()
    {
        $clientDate = '2025-11-01';

        $start = startDateToUtc($clientDate);
        $end = endDateToUtc($clientDate);

        $this->assertEquals('2025-11-01 00:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-11-01 23:59:59', $end->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_timezone()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone');

        startDateToUtc('2025-11-01', 'Invalid/Zone');
    }

    /** @test */
    public function it_throws_exception_for_invalid_date()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date');

        endDateToUtc('not-a-date', 'Asia/Ho_Chi_Minh');
    }
}