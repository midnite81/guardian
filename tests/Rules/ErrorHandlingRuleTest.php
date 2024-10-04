<?php

declare(strict_types=1);

use Midnite81\Guardian\Enums\Interval;
use Midnite81\Guardian\Rules\ErrorHandlingRule;

uses()->group('rules');

test('allowFailures creates an instance of ErrorHandlingRule', function () {
    $rule = ErrorHandlingRule::allowFailures(5);
    expect($rule)->toBeInstanceOf(ErrorHandlingRule::class)
        ->and($rule->getFailureThreshold())->toBe(5);
});

test('perInterval sets interval and duration', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perInterval(Interval::HOUR, 2);
    expect($rule->getInterval())->toBe(Interval::HOUR)
        ->and($rule->getDuration())->toBe(2);
});

test('perMinute sets correct interval', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perMinute();
    expect($rule->getInterval())->toBe(Interval::MINUTE)
        ->and($rule->getDuration())->toBe(1);
});

test('perHour sets correct interval', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perHour();
    expect($rule->getInterval())->toBe(Interval::HOUR)
        ->and($rule->getDuration())->toBe(1);
});

test('perDay sets correct interval', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perDay();
    expect($rule->getInterval())->toBe(Interval::DAY)
        ->and($rule->getDuration())->toBe(1);
});

test('perMinutes sets correct interval and duration', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perMinutes(5);
    expect($rule->getInterval())->toBe(Interval::MINUTE)
        ->and($rule->getDuration())->toBe(5);
});

test('perHours sets correct interval and duration', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perHours(12);
    expect($rule->getInterval())->toBe(Interval::HOUR)
        ->and($rule->getDuration())->toBe(12);
});

test('perDays sets correct interval and duration', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perDays(7);
    expect($rule->getInterval())->toBe(Interval::DAY)
        ->and($rule->getDuration())->toBe(7);
});

test('untilMidnightTonight sets expiration to next midnight', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->untilMidnightTonight();
    $expectedMidnight = (new DateTimeImmutable)->modify('tomorrow midnight');
    expect($rule->getUntil())->toEqual($expectedMidnight);
});

test('thenThrow sets shouldThrow flag', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->thenThrow(true);
    expect($rule->shouldThrow())->toBeTrue();

    $rule = ErrorHandlingRule::allowFailures(3)->thenThrow(false);
    expect($rule->shouldThrow())->toBeFalse();
});

test('getTotalSeconds calculates correct total seconds', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perInterval(Interval::HOUR, 2);
    expect($rule->getTotalSeconds())->toBe(7200); // 2 hours = 7200 seconds
});

test('getTotalSeconds calculates correct total seconds for perMinutes', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perMinutes(30);
    expect($rule->getTotalSeconds())->toBe(1800); // 30 minutes = 1800 seconds
});

test('getTotalSeconds calculates correct total seconds for perHours', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perHours(6);
    expect($rule->getTotalSeconds())->toBe(21600); // 6 hours = 21600 seconds
});

test('getTotalSeconds calculates correct total seconds for perDays', function () {
    $rule = ErrorHandlingRule::allowFailures(3)->perDays(2);
    expect($rule->getTotalSeconds())->toBe(172800); // 2 days = 172800 seconds
});
