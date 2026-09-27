<?php

declare(strict_types=1);

namespace diegocostadev\hotelbooking\tests\unit\domain;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use diegocostadev\hotelbooking\domain\InvalidStayException;
use diegocostadev\hotelbooking\domain\StayDates;

final class StayDatesTest extends Unit
{
    public function testNightsExcludeCheckOutDay(): void
    {
        $stay = StayDates::fromStrings('2026-12-30', '2027-01-02');

        $this->assertSame(['2026-12-30', '2026-12-31', '2027-01-01'], $stay->nights());
        $this->assertSame(3, $stay->nightCount());
        $this->assertSame('2026-12-30', $stay->checkIn());
        $this->assertSame('2027-01-02', $stay->checkOut());
    }

    public function testDstChangeDoesNotShiftNights(): void
    {
        // US DST ends 2026-11-01; UTC-midnight parsing keeps every night intact.
        $stay = StayDates::fromStrings('2026-10-31', '2026-11-02');

        $this->assertSame(['2026-10-31', '2026-11-01'], $stay->nights());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidStays(): array
    {
        return [
            'same day' => ['2026-12-01', '2026-12-01'],
            'check-out before check-in' => ['2026-12-05', '2026-12-01'],
            'impossible date' => ['2026-02-30', '2026-03-02'],
            'wrong format' => ['01/12/2026', '2026-12-03'],
            'datetime' => ['2026-12-01 10:00', '2026-12-03'],
        ];
    }

    #[DataProvider('invalidStays')]
    public function testInvalidStaysAreRejected(string $checkIn, string $checkOut): void
    {
        $this->expectException(InvalidStayException::class);

        StayDates::fromStrings($checkIn, $checkOut);
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function overlapCases(): array
    {
        // Existing stay: 2026-12-10 → 2026-12-15
        return [
            'same dates' => ['2026-12-10', '2026-12-15', true],
            'starts inside' => ['2026-12-12', '2026-12-18', true],
            'ends inside' => ['2026-12-08', '2026-12-11', true],
            'fully containing' => ['2026-12-01', '2026-12-31', true],
            'fully contained' => ['2026-12-11', '2026-12-12', true],
            'adjacent after' => ['2026-12-15', '2026-12-17', false],
            'adjacent before' => ['2026-12-05', '2026-12-10', false],
            'disjoint' => ['2026-12-20', '2026-12-22', false],
        ];
    }

    #[DataProvider('overlapCases')]
    public function testOverlap(string $checkIn, string $checkOut, bool $expected): void
    {
        $existing = StayDates::fromStrings('2026-12-10', '2026-12-15');
        $candidate = StayDates::fromStrings($checkIn, $checkOut);

        $this->assertSame($expected, $candidate->overlaps($existing));
        $this->assertSame($expected, $existing->overlaps($candidate));
    }
}
