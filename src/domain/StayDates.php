<?php

declare(strict_types=1);

namespace diegocostadev\hotelbooking\domain;

use DateTimeImmutable;
use DateTimeZone;

/**
 * A stay as a pair of calendar dates: the guest occupies the nights `[checkIn, checkOut)`.
 *
 * Dates are calendar dates, not instants. They are parsed as UTC midnight so that no
 * timezone or DST conversion can ever shift a day.
 */
final class StayDates
{
    private const FORMAT = 'Y-m-d';

    private function __construct(
        private readonly DateTimeImmutable $checkIn,
        private readonly DateTimeImmutable $checkOut,
    ) {
    }

    /**
     * @param string $checkIn `Y-m-d`
     * @param string $checkOut `Y-m-d`
     * @throws InvalidStayException
     */
    public static function fromStrings(string $checkIn, string $checkOut): self
    {
        $in = self::parse($checkIn, 'check-in');
        $out = self::parse($checkOut, 'check-out');

        if ($in >= $out) {
            throw new InvalidStayException('Check-out must be after check-in.');
        }

        return new self($in, $out);
    }

    public function checkIn(): string
    {
        return $this->checkIn->format(self::FORMAT);
    }

    public function checkOut(): string
    {
        return $this->checkOut->format(self::FORMAT);
    }

    public function nightCount(): int
    {
        return (int)$this->checkIn->diff($this->checkOut)->days;
    }

    /**
     * The occupied nights, `checkIn … checkOut - 1`, as `Y-m-d` strings.
     *
     * @return list<string>
     */
    public function nights(): array
    {
        $nights = [];
        for ($date = $this->checkIn; $date < $this->checkOut; $date = $date->modify('+1 day')) {
            $nights[] = $date->format(self::FORMAT);
        }

        return $nights;
    }

    /**
     * Two stays conflict iff each one starts before the other ends. Adjacent stays
     * (one's check-out == the other's check-in) do not conflict.
     */
    public function overlaps(self $other): bool
    {
        return $this->checkIn < $other->checkOut && $other->checkIn < $this->checkOut;
    }

    private static function parse(string $value, string $label): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!' . self::FORMAT, $value, new DateTimeZone('UTC'));

        if ($date === false || $date->format(self::FORMAT) !== $value) {
            throw new InvalidStayException(sprintf('Invalid %s date "%s"; expected Y-m-d.', $label, $value));
        }

        return $date;
    }
}
