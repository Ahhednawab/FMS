<?php

namespace Tests\Unit;

use App\Services\VehicleMaintenanceScheduleService;
use PHPUnit\Framework\TestCase;

/**
 * The arithmetic behind "when is this vehicle next due?".
 *
 * Worth its own tests because getting it wrong is invisible until a client
 * notices. The previous rule took the next multiple of the interval above the
 * last service reading, which meant a service done shortly before the mark was
 * due again almost at once: LE-5140 was serviced at 14,840 KM and fell due at
 * 15,000, so the alert came back 190 KM later and looked like the maintenance
 * had never been recorded.
 */
class MaintenanceDueMileageTest extends TestCase
{
    private VehicleMaintenanceScheduleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new VehicleMaintenanceScheduleService();
    }

    /** The three vehicles the client reported, with their real readings. */
    public function test_a_service_is_followed_by_a_full_interval(): void
    {
        // LE-5140: serviced at 14,840 on a 5,000 KM interval.
        $this->assertSame(20000, $this->service->nextDueFrom(14840, 5000));

        // LE-9924: serviced at 9,835.
        $this->assertSame(15000, $this->service->nextDueFrom(9835, 5000));

        // LF-1068: serviced at 8,401.
        $this->assertSame(14000, $this->service->nextDueFrom(8401, 5000));
    }

    /** Whatever else changes, the gap is never shorter than the interval. */
    public function test_the_gap_is_never_less_than_the_interval(): void
    {
        foreach ([5000, 20000, 40000, 50000] as $interval) {
            foreach ([0, 1, 999, 1000, 8401, 9835, 14840, 27293, 123456] as $reference) {
                $due = $this->service->nextDueFrom($reference, $interval);

                $this->assertGreaterThanOrEqual(
                    $reference + $interval,
                    $due,
                    "serviced at $reference on a $interval interval"
                );
            }
        }
    }

    /**
     * Rounding is to the next 1,000, not to a multiple of the interval.
     * Rounding to the interval pushed Power Steering Oil - a 50,000 KM item -
     * from 77,293 all the way to 100,000, which is 22,707 KM late.
     */
    public function test_rounding_never_pushes_a_service_far_out(): void
    {
        $this->assertSame(78000, $this->service->nextDueFrom(27293, 50000));

        foreach ([5000, 20000, 40000, 50000] as $interval) {
            foreach ([27293, 8401, 14840, 61111] as $reference) {
                $due = $this->service->nextDueFrom($reference, $interval);

                $this->assertLessThan(
                    $reference + $interval + VehicleMaintenanceScheduleService::DUE_ROUNDING_KM,
                    $due,
                    "serviced at $reference on a $interval interval"
                );
            }
        }
    }

    public function test_due_readings_come_out_round(): void
    {
        foreach ([5000, 20000, 40000, 50000] as $interval) {
            foreach ([8401, 9835, 14840, 27293] as $reference) {
                $this->assertSame(
                    0,
                    $this->service->nextDueFrom($reference, $interval) % 1000,
                    "serviced at $reference on a $interval interval"
                );
            }
        }
    }

    /** A service exactly on a round reading gains nothing extra. */
    public function test_an_exact_reading_is_not_pushed_further(): void
    {
        $this->assertSame(15000, $this->service->nextDueFrom(10000, 5000));
        $this->assertSame(60000, $this->service->nextDueFrom(20000, 40000));
        $this->assertSame(5000, $this->service->nextDueFrom(0, 5000));
    }

    /** An interval below the rounding step is used exactly, not rounded. */
    public function test_a_small_interval_is_left_alone(): void
    {
        $this->assertSame(13116, $this->service->nextDueFrom(12616, 500));
        $this->assertSame(1100, $this->service->nextDueFrom(1000, 100));
    }

    public function test_a_missing_interval_cannot_create_a_due_reading(): void
    {
        $this->assertSame(12616, $this->service->nextDueFrom(12616, 0));
        $this->assertSame(12616, $this->service->nextDueFrom(12616, -5000));
    }
}
