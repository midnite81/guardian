<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Enums;

enum Interval: string
{
    case SECOND = 'second';
    case MINUTE = 'minute';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';

    /**
     * Converts the interval to its equivalent in days.
     *
     * @return float The equivalent of the interval in days.
     */
    public function toDays(): float
    {
        return match ($this) {
            self::SECOND => 1 / 86400,
            self::MINUTE => 1 / 1440,
            self::HOUR => 1 / 24,
            self::DAY => 1,
            self::WEEK => 7,
            self::MONTH => 30.44, // Average month length
        };
    }

    /**
     * Converts the interval to its equivalent in seconds.
     *
     * @return int The equivalent of the interval in seconds.
     */
    public function toSeconds(): int
    {
        return match ($this) {
            self::SECOND => 1,
            self::MINUTE => 60,
            self::HOUR => 3600,
            self::DAY => 86400,
            self::WEEK => 604800,
            self::MONTH => 2629746, // Average month length in seconds
        };
    }
}
