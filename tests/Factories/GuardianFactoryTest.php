<?php

/** @noinspection PhpParamsInspection */

declare(strict_types=1);

use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Factories\GuardianFactory;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\RateLimitRule;

uses()->group('factories');

it('constructs a factory using the create method with custom parameters', function () {
    $name = 'test-guardian';
    $customCache = Mockery::mock(CacheInterface::class);
    $customRules = Mockery::mock(RateLimitingRulesetInterface::class);
    $customRules->shouldReceive('rules')->andReturn([
        RateLimitRule::allow(5)->perDay(),
    ]);
    $customErrorRules = Mockery::mock(ErrorHandlingRulesetInterface::class);

    $guardian = GuardianFactory::create($name, $customCache, $customRules, $customErrorRules);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBe($customCache)
        ->and($guardian->getRules())->toBe($customRules)
        ->and($guardian->getErrorRules())->toBe($customErrorRules)
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian');
});

it('it throws if too few arguments', function () {
    expect(fn () => GuardianFactory::create('test-guardian'))
        ->toThrow(ArgumentCountError::class);
});

it('constructs a factory using the create method with partial custom parameters', function () {
    $name = 'test-guardian';
    $customCache = Mockery::mock(CacheInterface::class);

    $guardian = GuardianFactory::create($name, $customCache);

    expect($guardian)->toBeInstanceOf(Guardian::class)
        ->and($guardian->getCache())->toBe($customCache)
        ->and($guardian->getRules())->toBeNull()
        ->and($guardian->getErrorRules())->toBeNull()
        ->and($guardian->getIdentifier())->toBe('guardian_test-guardian');
});
