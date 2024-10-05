<?php

declare(strict_types=1);

use Midnite81\Guardian\Exceptions\RateLimitExceededException;

test('RateLimitExceededException accepts DateTimeImmutable', function () {
    $now = new DateTimeImmutable;
    $exception = new RateLimitExceededException($now);

    expect($exception->getRetryAfter()->getTimestamp())->toBe($now->getTimestamp());
});

test('RateLimitExceededException accepts integer seconds', function () {
    $seconds = 3600;
    $beforeException = new DateTimeImmutable;
    $exception = new RateLimitExceededException($seconds);
    $afterException = new DateTimeImmutable;

    $retryAfter = $exception->getRetryAfter();
    expect($retryAfter->getTimestamp())->toBeGreaterThanOrEqual($beforeException->getTimestamp() + $seconds)
        ->and($retryAfter->getTimestamp())->toBeLessThanOrEqual($afterException->getTimestamp() + $seconds);
});

test('RateLimitExceededException accepts string seconds', function () {
    $seconds = '3600';
    $beforeException = new DateTimeImmutable;
    $exception = new RateLimitExceededException($seconds);
    $afterException = new DateTimeImmutable;

    $retryAfter = $exception->getRetryAfter();
    expect($retryAfter->getTimestamp())->toBeGreaterThanOrEqual($beforeException->getTimestamp() + (int) $seconds)
        ->and($retryAfter->getTimestamp())->toBeLessThanOrEqual($afterException->getTimestamp() + (int) $seconds);
});

test('RateLimitExceededException accepts HTTP date string', function () {
    $httpDate = 'Wed, 21 Oct 2015 07:28:00 GMT';
    $exception = new RateLimitExceededException($httpDate);

    $expectedTime = DateTimeImmutable::createFromFormat('D, d M Y H:i:s \G\M\T', $httpDate);
    expect($exception->getRetryAfter()->format('Y-m-d H:i:s'))->toBe($expectedTime->format('Y-m-d H:i:s'));
});

test('RateLimitExceededException throws InvalidArgumentException for invalid string', function () {
    $invalidDate = 'Invalid Date String';

    expect(fn () => new RateLimitExceededException($invalidDate))
        ->toThrow(InvalidArgumentException::class, 'Invalid retry-after format. Expected DateTimeImmutable, integer, or valid HTTP-date string.');
});

test('RateLimitExceededException throws InvalidArgumentException for invalid type', function () {
    $invalidType = ['invalid'];

    expect(fn () => new RateLimitExceededException($invalidType))
        ->toThrow(InvalidArgumentException::class, 'Invalid retry-after format. Expected DateTimeImmutable, integer, or string.');
});

test('RateLimitExceededException message and code are set correctly', function () {
    $message = 'Custom error message';
    $code = 429;
    $exception = new RateLimitExceededException(3600, $message, $code);

    expect($exception->getMessage())->toBe($message)
        ->and($exception->getCode())->toBe($code);
});
