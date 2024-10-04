<?php

declare(strict_types=1);

use Midnite81\Guardian\Exceptions\RulePreventsExecutionException;
use Midnite81\Guardian\Rules\RateLimitRule;

it('throws a default message if preventingRule is null', function () {
    expect(fn () => throw new RulePreventsExecutionException(null))
        ->toThrow(
            RulePreventsExecutionException::class,
            'Cannot execute the request because a rule prevents it'
        );
});

it('returns null when no preventing rule is set', function () {
    $exception = new RulePreventsExecutionException;

    expect($exception->getPreventingRule())->toBeNull();
});

it('returns the preventing rule when set', function () {
    $rateLimitRule = RateLimitRule::allow(5)->perMinute();
    $exception = new RulePreventsExecutionException($rateLimitRule);

    expect($exception->getPreventingRule())->toBe($rateLimitRule);
});
