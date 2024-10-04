<?php

declare(strict_types=1);

use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Enums\Interval;
use Midnite81\Guardian\Factories\LaravelGuardianFactory;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Rulesets\GenericErrorHandlingRuleset;
use Midnite81\Guardian\Rulesets\GenericRateLimitingRuleset;
use Midnite81\Guardian\Store\LaravelStore;

uses(\Midnite81\Guardian\Tests\OrchestraTestCase::class)->group('factories');

it('constructs with laravel cache via make method', function () {
    $guardian = LaravelGuardianFactory::make(uniqid());
    $sut = $guardian->getCache();

    expect($sut)->toBeInstanceOf(LaravelStore::class);
});

it('constructs with rules via make method', function () {
    $name = 'test-guardian';
    $rules = [
        RateLimitRule::allow(5)->perMinute(),
        RateLimitRule::allow(1000)->perDay(),
    ];

    $guardian = LaravelGuardianFactory::make($name, $rules);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBeInstanceOf(LaravelStore::class)
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian')
        ->and($guardian->getRules())->toBeInstanceOf(GenericRateLimitingRuleset::class)
        ->and($guardian->getRules()->getRules())->toHaveCount(2)
        ->and($guardian->getRules()->getRules()[0])->toBeInstanceOf(RateLimitRule::class)
        ->and($guardian->getRules()->getRules()[1])->toBeInstanceOf(RateLimitRule::class)
        ->and($guardian->getRules()->getRules()[0]->getLimit())->toBe(5)
        ->and($guardian->getRules()->getRules()[0]->getInterval())->toBe(Interval::MINUTE)
        ->and($guardian->getRules()->getRules()[1]->getLimit())->toBe(1000)
        ->and($guardian->getRules()->getRules()[1]->getInterval())->toBe(Interval::DAY);
});

it('constructs with error rules via make method', function () {
    $name = 'test-guardian';
    $errorRules = [
        ErrorHandlingRule::allowFailures(3)->perMinute()->thenThrow(),
        ErrorHandlingRule::allowFailures(5)->perHour()->thenThrow(),
    ];

    $guardian = LaravelGuardianFactory::make($name, null, $errorRules);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBeInstanceOf(LaravelStore::class)
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian')
        ->and($guardian->getErrorRules())->toBeInstanceOf(GenericErrorHandlingRuleset::class)
        ->and($guardian->getErrorRules()->getRules())->toHaveCount(2)
        ->and($guardian->getErrorRules()->getRules()[0])->toBeInstanceOf(ErrorHandlingRule::class)
        ->and($guardian->getErrorRules()->getRules()[1])->toBeInstanceOf(ErrorHandlingRule::class);
});

it('constructs with custom parameters via create method', function () {
    $name = 'test-guardian';
    $customCache = Mockery::mock(CacheInterface::class);
    $customRules = Mockery::mock(RateLimitingRulesetInterface::class);
    $customRules->shouldReceive('rules')->andReturn([
        RateLimitRule::allow(5)->perDay(),
    ]);
    $customErrorRules = Mockery::mock(ErrorHandlingRulesetInterface::class);

    $guardian = LaravelGuardianFactory::create($name, $customCache, $customRules, $customErrorRules);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBeInstanceOf(CacheInterface::class)
        ->and($guardian->getRules())->toBeInstanceOf(RateLimitingRulesetInterface::class)
        ->and($guardian->getErrorRules())->toBeInstanceOf(ErrorHandlingRulesetInterface::class)
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian');
});

it('constructs with default LaravelStore when no cache is provided', function () {
    $name = 'test-guardian';

    $guardian = LaravelGuardianFactory::create($name);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBeInstanceOf(LaravelStore::class)
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian');
});

it('constructs with null rules and error rules when not provided', function () {
    $name = 'test-guardian';
    $customCache = Mockery::mock(CacheInterface::class);

    $guardian = LaravelGuardianFactory::create($name, $customCache);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBeInstanceOf(CacheInterface::class)
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian')
        ->and($guardian->getRules())->toBeNull()
        ->and($guardian->getErrorRules())->toBeNull();
});
