<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Rules;

use DateTimeImmutable;
use InvalidArgumentException;
use Midnite81\Guardian\Enums\Interval;

/**
 * RateLimitRule class
 *
 * This class represents a rule for rate limiting in the Guardian system.
 */
class RateLimitRule
{
    /**
     * @var int The rate limit
     */
    private int $limit;

    /**
     * @var Interval The interval of the rate limit
     */
    private Interval $interval;

    /**
     * @var string|null The key for the rate limit
     */
    private ?string $key = null;

    /**
     * @var int The duration of the rate limit
     */
    private int $duration = 1;

    /**
     * @var DateTimeImmutable|null The expiration time of the rate limit
     */
    private ?DateTimeImmutable $until = null;

    /**
     * Allow a specific limit.
     *
     * @param int $limit The rate limit
     * @return self
     */
    public static function allow(int $limit): self
    {
        $instance = new self;
        $instance->limit = $limit;

        return $instance;
    }

    /**
     * Set the interval for the rate limit.
     *
     * @param int $amount The amount of time units
     * @param Interval $unit The time unit
     * @return self
     */
    public function every(int $amount, Interval $unit): self
    {
        $this->interval = $unit;
        $this->duration = $amount;

        return $this;
    }

    /**
     * Set the rate limit to per second.
     *
     * @return self
     */
    public function perSecond(): self
    {
        return $this->every(1, Interval::SECOND);
    }

    /**
     * Set the rate limit to per number of seconds.
     *
     * @param int $seconds The number of seconds
     * @return self
     */
    public function perSeconds(int $seconds): self
    {
        return $this->every($seconds, Interval::SECOND);
    }

    /**
     * Set the rate limit to per minute.
     *
     * @return self
     */
    public function perMinute(): self
    {
        return $this->every(1, Interval::MINUTE);
    }

    /**
     * Set the rate limit to per number of minutes.
     *
     * @param int $minutes The number of minutes
     * @return self
     */
    public function perMinutes(int $minutes): self
    {
        return $this->every($minutes, Interval::MINUTE);
    }

    /**
     * Set the rate limit to per hour.
     *
     * @return self
     */
    public function perHour(): self
    {
        return $this->every(1, Interval::HOUR);
    }

    /**
     * Set the rate limit to per number of hours.
     *
     * @param int $hours The number of hours
     * @return self
     */
    public function perHours(int $hours): self
    {
        return $this->every($hours, Interval::HOUR);
    }

    /**
     * Set the rate limit to per day.
     *
     * @return self
     */
    public function perDay(): self
    {
        return $this->every(1, Interval::DAY);
    }

    /**
     * Set the rate limit to per number of days.
     *
     * @param int $days The number of days
     * @return self
     */
    public function perDays(int $days): self
    {
        return $this->every($days, Interval::DAY);
    }

    /**
     * Set the rate limit to per week.
     *
     * @return self
     */
    public function perWeek(): self
    {
        return $this->every(1, Interval::WEEK);
    }

    /**
     * Set the rate limit to per number of weeks.
     *
     * @param int $weeks The number of weeks
     * @return self
     */
    public function perWeeks(int $weeks): self
    {
        return $this->every($weeks, Interval::WEEK);
    }

    /**
     * Set the rate limit to per month.
     *
     * @return self
     */
    public function perMonth(): self
    {
        return $this->every(1, Interval::MONTH);
    }

    /**
     * Set the rate limit to per number of months.
     *
     * @param int $months The number of months
     * @return self
     */
    public function perMonths(int $months): self
    {
        return $this->every($months, Interval::MONTH);
    }

    /**
     * Set the rate limit to be applied daily until a specific time.
     *
     * @param string $time The time in 'H:i' format
     * @return self
     *
     * @throws InvalidArgumentException If the time format is invalid
     */
    public function dailyUntil(string $time): self
    {
        $now = new DateTimeImmutable;
        $until = DateTimeImmutable::createFromFormat('H:i', $time);
        if ($until === false) {
            throw new InvalidArgumentException("Invalid time format. Use 'H:i'.");
        }
        $this->until = $until > $now ? $until : $until->modify('+1 day');

        return $this;
    }

    /**
     * Set the rate limit until midnight tonight.
     *
     * @return self
     */
    public function untilMidnightTonight(): self
    {
        $this->until = (new DateTimeImmutable)->modify('tomorrow midnight');

        return $this;
    }

    /**
     * Set the rate limit until the end of the month.
     *
     * @return self
     */
    public function untilEndOfMonth(): self
    {
        $this->until = (new DateTimeImmutable)->modify('last day of this month 23:59:59');

        return $this;
    }

    /**
     * Get the rate limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Get the interval of the rate limit.
     *
     * @return Interval
     */
    public function getInterval(): Interval
    {
        return $this->interval;
    }

    /**
     * Get the duration of the rate limit.
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * Get the expiration time of the rate limit.
     *
     * @return DateTimeImmutable|null
     */
    public function getUntil(): ?DateTimeImmutable
    {
        return $this->until;
    }

    /**
     * Get the total seconds for the rate limit.
     *
     * @return int
     */
    public function getTotalSeconds(): int
    {
        return $this->interval->toSeconds() * $this->duration;
    }

    /**
     * Get the key for the rate limit.
     *
     * @param string $prefix An optional prefix for the key
     * @param string $suffix An optional suffix for the key
     * @return string The generated key
     */
    public function getKey(string $prefix = '', string $suffix = ''): string
    {
        if ($this->key === null) {
            $this->key = $this->generateKey($prefix, $suffix);
        }

        return $this->key;
    }

    /**
     * Protected constructor to prevent creating a new instance of the class via the `new` operator.
     */
    protected function __construct()
    {
    }

    /**
     * Generate the key for the rate limit.
     *
     * @param string $prefix An optional prefix for the key
     * @param string $suffix An optional suffix for the key
     * @return string The generated key
     */
    protected function generateKey(string $prefix = '', string $suffix = ''): string
    {
        return sprintf(
            '%srate_limit_%d_per_%s%s%s%s',
            $prefix,
            $this->limit,
            $this->interval->value,
            $this->duration > 1 ? "_{$this->duration}" : '',
            $this->until !== null ? '_until_' . $this->until->format('YmdHis') : '',
            $suffix
        );
    }
}
