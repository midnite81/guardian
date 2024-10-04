<?php

/** @noinspection PhpParamsInspection */

declare(strict_types=1);

use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Rulesets\GenericRateLimitingRuleset;

uses()->group('rulesets');

beforeEach(function () {
    $this->genericRuleset = new GenericRateLimitingRuleset;
});

test('rules method returns an empty array by default', function () {
    expect($this->genericRuleset->getRules())->toBeArray()->toBeEmpty();
});

test('addRules method adds rules and returns self', function () {
    $rule1 = RateLimitRule::allow(100)->perMinute();
    $rule2 = RateLimitRule::allow(1000)->perHour();

    $result = $this->genericRuleset->addRules([$rule1, $rule2]);

    expect($result)->toBeInstanceOf(GenericRateLimitingRuleset::class)
        ->and($result)->toBe($this->genericRuleset)
        ->and($this->genericRuleset->getRules())->toBe([$rule1, $rule2]);
});

test('rules method returns added rules', function () {
    $rule1 = RateLimitRule::allow(100)->perMinute();
    $rule2 = RateLimitRule::allow(1000)->perHour();

    $this->genericRuleset->addRules([$rule1, $rule2]);

    expect($this->genericRuleset->getRules())->toBe([$rule1, $rule2]);
});

test('addRules method overwrites existing rules', function () {
    $rule1 = RateLimitRule::allow(100)->perMinute();
    $rule2 = RateLimitRule::allow(1000)->perHour();
    $rule3 = RateLimitRule::allow(50)->perSecond();

    $this->genericRuleset->addRules([$rule1, $rule2]);
    $this->genericRuleset->addRules([$rule3]);

    expect($this->genericRuleset->getRules())->toBe([$rule1, $rule2, $rule3]);
});

test('addRules method accepts an empty array', function () {
    $this->genericRuleset->addRules([]);

    expect($this->genericRuleset->getRules())->toBeArray()->toBeEmpty();
});

test('addRules method throws TypeError for non-RateLimitRule items', function () {
    expect(fn () => $this->genericRuleset->addRules([new stdClass]))
        ->toThrow(InvalidArgumentException::class,
            'Each item in the array must be an instance of Midnite81\Guardian\Rules\RateLimitRule');
});

test('addRules method throws TypeError for non-array input', function () {
    expect(fn () => $this->genericRuleset->addRules('not an array'))
        ->toThrow(TypeError::class);
});

test('rules method returns a copy of the rules array', function () {
    $rule1 = RateLimitRule::allow(100)->perMinute();
    $rule2 = RateLimitRule::allow(1000)->perHour();

    $this->genericRuleset->addRules([$rule1, $rule2]);

    $rules = $this->genericRuleset->getRules();
    $rules[] = RateLimitRule::allow(50)->perSecond();

    expect($this->genericRuleset->getRules())->toBe([$rule1, $rule2]);
});

test('addRule method adds a single rule and returns self', function () {
    $rule = RateLimitRule::allow(100)->perMinute();

    $result = $this->genericRuleset->addRule($rule);

    expect($result)->toBeInstanceOf(GenericRateLimitingRuleset::class)
        ->and($result)->toBe($this->genericRuleset)
        ->and($this->genericRuleset->getRules())->toBe([$rule]);
});

test('addRule method appends rule to existing rules', function () {
    $rule1 = RateLimitRule::allow(100)->perMinute();
    $rule2 = RateLimitRule::allow(1000)->perHour();

    $this->genericRuleset->addRule($rule1);
    $this->genericRuleset->addRule($rule2);

    expect($this->genericRuleset->getRules())->toBe([$rule1, $rule2]);
});

it('provides an array of rules', function () {
    expect($this->genericRuleset->rules())->toBeArray()->toBeEmpty();
});
