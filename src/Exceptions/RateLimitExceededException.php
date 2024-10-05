<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Exceptions;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

class RateLimitExceededException extends Exception
{
    protected DateTimeImmutable $retryAfter;

    /**
     * @param mixed $retryAfter Can be a DateTimeImmutable, an integer (seconds), or a string (HTTP-date or seconds)
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     *
     * @throws InvalidArgumentException
     */
    public function __construct(mixed $retryAfter, string $message = '', int $code = 0, ?Exception $previous = null)
    {
        $this->setRetryAfter($retryAfter);
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieves the retry-after duration as a DateTimeImmutable instance.
     *
     * @return DateTimeImmutable The retry-after duration.
     */
    public function getRetryAfter(): DateTimeImmutable
    {
        return $this->retryAfter;
    }

    /**
     * Sets the retry-after value.
     *
     * @param mixed $retryAfter Can be a DateTimeImmutable, an integer (seconds), or a string (HTTP-date or seconds)
     * @return void
     *
     * @throws InvalidArgumentException Thrown if the provided retry-after value is not valid
     */
    protected function setRetryAfter(mixed $retryAfter): void
    {
        if ($retryAfter instanceof DateTimeImmutable) {
            $this->retryAfter = $retryAfter;
        } elseif (is_int($retryAfter)) {
            $this->retryAfter = (new DateTimeImmutable)->modify("+$retryAfter seconds");
        } elseif (is_string($retryAfter)) {
            if (ctype_digit($retryAfter)) {
                $this->retryAfter = (new DateTimeImmutable)->modify("+$retryAfter seconds");
            } else {
                $date = DateTimeImmutable::createFromFormat('D, d M Y H:i:s \G\M\T', $retryAfter);
                if ($date === false) {
                    throw new InvalidArgumentException(
                        'Invalid retry-after format. Expected DateTimeImmutable, integer, or valid HTTP-date string.'
                    );
                }
                $this->retryAfter = $date;
            }
        } else {
            throw new InvalidArgumentException(
                'Invalid retry-after format. Expected DateTimeImmutable, integer, or string.'
            );
        }
    }
}
