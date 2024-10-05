<?php

declare(strict_types=1);

use Midnite81\Guardian\Enums\Interval;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Rulesets\GenericRateLimitingRuleset;
use Midnite81\Guardian\Store\LaravelStore;
use Midnite81\Guardian\Tests\OrchestraTestCase;

uses(OrchestraTestCase::class)->group('providers');

test('RequestGuardian is bound in the container', function () {
    expect($this->app->make(Guardian::class))->toBeInstanceOf(Guardian::class);
});

test('RequestGuardian is instantiated with LaravelStore', function () {
    $guardian = $this->app->make(Guardian::class);

    expect($guardian->getCache())->toBeInstanceOf(LaravelStore::class);
});

test('guardian.factory is bound in the container and can create RequestGuardian instances', function () {
    $factory = $this->app->make('guardian.factory');

    expect($factory)->toBeObject()
        ->and(method_exists($factory, 'make'))->toBeTrue();

    $guardian = $factory->make(uniqid());
    expect($guardian)->toBeInstanceOf(Guardian::class);
});

test('guardian.factory creates RequestGuardian instance', function () {
    $factory = $this->app->make('guardian.factory');
    $guardian = $factory->make(uniqid());

    expect($guardian)->toBeInstanceOf(Guardian::class);
});

test('guardian.factory adds rules when provided', function () {
    $factory = $this->app->make('guardian.factory');
    $rules = [
        RateLimitRule::allow(5)->perMinute(),
        RateLimitRule::allow(5000)->perMonth(),
    ];
    $guardian = $factory->make(uniqid(), $rules);

    $returnedRules = $guardian->getRules();
    expect($returnedRules)->toBeInstanceOf(GenericRateLimitingRuleset::class);

    $returnedRulesArray = $returnedRules->getRules();
    expect($returnedRulesArray)->toHaveCount(2)
        ->and($returnedRulesArray[0])->toBeInstanceOf(RateLimitRule::class)
        ->and($returnedRulesArray[1])->toBeInstanceOf(RateLimitRule::class)
        ->and($returnedRulesArray[0]->getLimit())->toBe(5)
        ->and($returnedRulesArray[0]->getInterval())->toBe(Interval::MINUTE)
        ->and($returnedRulesArray[1]->getLimit())->toBe(5000)
        ->and($returnedRulesArray[1]->getInterval())->toBe(Interval::MONTH);
});

test('guardian.factory sets error rule when provided', function () {
    $factory = $this->app->make('guardian.factory');
    $errorRules = [
        \Midnite81\Guardian\Rules\ErrorHandlingRule::allowFailures(10)->perHour()->thenThrow(),
    ];

    $guardian = $factory->make(uniqid(), [], $errorRules);
    $sut = $guardian->getErrorRules()->getRules();

    expect($sut)->toHaveCount(1)
        ->and($sut)->toBe($errorRules);
});

it('registers the alias if the version is 11 or greater', function () {
    $app = Mockery::mock('Illuminate\Contracts\Foundation\Application')->makePartial();
    $provider = new Midnite81\Guardian\Providers\GuardianServiceProvider($app);

    $app->shouldReceive('version')->andReturn('11.0.0');
    $app->shouldReceive('alias')->once();
    $app->shouldReceive('bind');
    $provider->register();

    \PHPUnit\Framework\Assert::assertTrue(true);
    Mockery::close();
});
