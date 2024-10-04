<?php

declare(strict_types=1);

use Midnite81\Guardian\Enums\Interval;
use Midnite81\Guardian\Rules\RateLimitRule;

uses()->group('rules');

it('can create a rate limit rule', function () {
    $rule = RateLimitRule::allow(5);
    expect($rule)->toBeInstanceOf(RateLimitRule::class)
        ->and($rule->getLimit())->toBe(5);
});

it('can set limit per second', function () {
    $rule = RateLimitRule::allow(10)->perSecond();
    expect($rule->getInterval())->toBe(Interval::SECOND)
        ->and($rule->getDuration())->toBe(1);
});

it('can set limit per multiple seconds', function () {
    $rule = RateLimitRule::allow(20)->perSeconds(30);
    expect($rule->getInterval())->toBe(Interval::SECOND)
        ->and($rule->getDuration())->toBe(30);
});

it('can set limit per minute', function () {
    $rule = RateLimitRule::allow(30)->perMinute();
    expect($rule->getInterval())->toBe(Interval::MINUTE)
        ->and($rule->getDuration())->toBe(1);
});

it('can set limit per multiple minutes', function () {
    $rule = RateLimitRule::allow(40)->perMinutes(15);
    expect($rule->getInterval())->toBe(Interval::MINUTE)
        ->and($rule->getDuration())->toBe(15);
});

it('can set limit per hour', function () {
    $rule = RateLimitRule::allow(50)->perHour();
    expect($rule->getInterval())->toBe(Interval::HOUR)
        ->and($rule->getDuration())->toBe(1);
});

it('can set limit per multiple hours', function () {
    $rule = RateLimitRule::allow(60)->perHours(6);
    expect($rule->getInterval())->toBe(Interval::HOUR)
        ->and($rule->getDuration())->toBe(6);
});

it('can set limit per day', function () {
    $rule = RateLimitRule::allow(70)->perDay();
    expect($rule->getInterval())->toBe(Interval::DAY)
        ->and($rule->getDuration())->toBe(1);
});

it('can set limit per multiple days', function () {
    $rule = RateLimitRule::allow(80)->perDays(7);
    expect($rule->getInterval())->toBe(Interval::DAY)
        ->and($rule->getDuration())->toBe(7);
});

it('can set limit per week', function () {
    $rule = RateLimitRule::allow(90)->perWeek();
    expect($rule->getInterval())->toBe(Interval::WEEK)
        ->and($rule->getDuration())->toBe(1);
});

it('can set limit per multiple weeks', function () {
    $rule = RateLimitRule::allow(100)->perWeeks(2);
    expect($rule->getInterval())->toBe(Interval::WEEK)
        ->and($rule->getDuration())->toBe(2);
});

it('can set limit per month', function () {
    $rule = RateLimitRule::allow(110)->perMonth();
    expect($rule->getInterval())->toBe(Interval::MONTH)
        ->and($rule->getDuration())->toBe(1);
});

it('can set limit per multiple months', function () {
    $rule = RateLimitRule::allow(120)->perMonths(3);
    expect($rule->getInterval())->toBe(Interval::MONTH)
        ->and($rule->getDuration())->toBe(3);
});

it('can set daily until specific time', function () {
    $rule = RateLimitRule::allow(130)->perDay()->dailyUntil('23:59');
    expect($rule->getUntil())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($rule->getUntil()->format('H:i'))->toBe('23:59');
});

it('throws exception for invalid time format in dailyUntil', function () {
    expect(fn () => RateLimitRule::allow(140)->perDay()->dailyUntil('invalid'))
        ->toThrow(InvalidArgumentException::class, "Invalid time format. Use 'H:i'.");
});

it('can set until midnight tonight', function () {
    $rule = RateLimitRule::allow(150)->perDay()->untilMidnightTonight();
    $expectedMidnight = (new DateTimeImmutable)->modify('tomorrow midnight');
    expect($rule->getUntil()->format('Y-m-d H:i:s'))->toBe($expectedMidnight->format('Y-m-d H:i:s'));
});

it('can set until end of month', function () {
    $rule = RateLimitRule::allow(160)->perMonth()->untilEndOfMonth();
    $expectedEndOfMonth = (new DateTimeImmutable)->modify('last day of this month 23:59:59');
    expect($rule->getUntil()->format('Y-m-d H:i:s'))->toBe($expectedEndOfMonth->format('Y-m-d H:i:s'));
});

it('generates correct key', function () {
    $rule = RateLimitRule::allow(170)->perMinutes(5);
    expect($rule->getKey())->toBe('rate_limit_170_per_minute_5');
});

it('calculates total seconds correctly', function () {
    $rule = RateLimitRule::allow(180)->perMinutes(3);
    expect($rule->getTotalSeconds())->toBe(180); // 3 minutes = 180 seconds
});
