<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Rules;

use DateTimeImmutable;
use Midnite81\Guardian\Enums\Interval;

/**
 * ErrorHandlingRule class
 *
 * This class represents a rule for handling errors in the Guardian system.
 */
class ErrorHandlingRule
{
    /**
     * @var int The number of allowed failures
     */
    protected int $failureThreshold;

    /**
     * @var Interval|null The interval enum value
     */
    protected ?Interval $interval = null;

    /**
     * @var int The number of intervals
     */
    protected int $duration = 1;

    /**
     * @var DateTimeImmutable|null The expiration date/time
     */
    protected ?DateTimeImmutable $until = null;

    /**
     * @var bool Whether to throw an exception when the threshold is exceeded
     */
    protected bool $shouldThrow = true;

    /**
     * Static factory method to create a new ErrorHandlingRule instance.
     *
     * @param int $failureThreshold The number of allowed failures
     * @return self
     */
    public static function allowFailures(int $failureThreshold): self
    {
        return new self($failureThreshold);
    }

    /**
     * Set the interval and duration for the error handling rule.
     *
     * @param Interval $interval The interval enum value
     * @param int $duration The number of intervals
     * @return self
     */
    public function perInterval(Interval $interval, int $duration = 1): self
    {
        $this->interval = $interval;
        $this->duration = $duration;

        return $this;
    }

    /**
     * Set the interval to per minute.
     *
     * @return self
     */
    public function perMinute(): self
    {
        return $this->perInterval(Interval::MINUTE);
    }

    /**
     * Set the interval to a specified number of minutes.
     *
     * @param int $value The number of minutes for the interval
     * @return self
     */
    public function perMinutes(int $value): self
    {
        return $this->perInterval(Interval::MINUTE, $value);
    }

    /**
     * Set the interval to per hour.
     *
     * @return self
     */
    public function perHour(): self
    {
        return $this->perInterval(Interval::HOUR);
    }

    /**
     * Sets the interval to hours and assigns a value to it.
     *
     * @param int $value The number of hours to set for the interval
     * @return self
     */
    public function perHours(int $value): self
    {
        return $this->perInterval(Interval::HOUR, $value);
    }

    /**
     * Set the interval to per day.
     *
     * @return self
     */
    public function perDay(): self
    {
        return $this->perInterval(Interval::DAY);
    }

    /**
     * Set the period interval to days.
     *
     * @param int $value The number of days to set for the interval
     * @return self
     */
    public function perDays(int $value): self
    {
        return $this->perInterval(Interval::DAY, $value);
    }

    /**
     * Set the rule to expire at midnight tonight.
     *
     * @return self
     */
    public function untilMidnightTonight(): self
    {
        $this->until = (new DateTimeImmutable)->modify('tomorrow midnight');

        return $this;
    }

    /**
     * Set whether an exception should be thrown when the threshold is exceeded.
     *
     * @param bool $shouldThrow Whether to throw an exception
     * @return self
     */
    public function thenThrow(bool $shouldThrow = true): self
    {
        $this->shouldThrow = $shouldThrow;

        return $this;
    }

    /**
     * Get the failure threshold.
     *
     * @return int
     */
    public function getFailureThreshold(): int
    {
        return $this->failureThreshold;
    }

    /**
     * Get the interval.
     *
     * @return Interval|null
     */
    public function getInterval(): ?Interval
    {
        return $this->interval;
    }

    /**
     * Get the duration.
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * Get the expiration date/time.
     *
     * @return DateTimeImmutable|null
     */
    public function getUntil(): ?DateTimeImmutable
    {
        return $this->until;
    }

    /**
     * Check if an exception should be thrown when the threshold is exceeded.
     *
     * @return bool
     */
    public function shouldThrow(): bool
    {
        return $this->shouldThrow;
    }

    /**
     * Calculate the total number of seconds based on the interval and duration.
     *
     * @return int
     */
    public function getTotalSeconds(): int
    {
        return $this->interval ? $this->interval->toSeconds() * $this->duration : 0;
    }

    /**
     * Generate a unique key for this rule.
     *
     * @param string $prefix An optional prefix for the key
     * @param string $suffix An optional suffix for the key
     * @return string The generated key
     */
    public function getKey(string $prefix = '', string $suffix = ''): string
    {
        return sprintf(
            '%s%d_%s_%d_%s%s',
            $prefix,
            $this->failureThreshold,
            $this->interval ? $this->interval->value : 'none',
            $this->duration,
            $this->until ? $this->until->format('YmdHis') : 'no_expiry',
            $suffix
        );
    }

    /**
     * Private constructor to enforce usage of static factory method.
     *
     * @param int $failureThreshold The number of allowed failures
     */
    protected function __construct(int $failureThreshold)
    {
        $this->failureThreshold = $failureThreshold;
    }
}
