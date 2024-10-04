<?php

declare(strict_types=1);

use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rulesets\GenericErrorHandlingRuleset;

uses()->group('rulesets');

beforeEach(function () {
    $this->ruleset = new GenericErrorHandlingRuleset;
});

test('rules method returns an empty array initially', function () {
    expect($this->ruleset->getRules())->toBeArray()->toBeEmpty();
});

test('addRule method adds a single rule', function () {
    $rule = ErrorHandlingRule::allowFailures(5);
    $this->ruleset->addRule($rule);

    expect($this->ruleset->getRules())
        ->toBeArray()
        ->toHaveCount(1)
        ->toContain($rule);
});

test('addRule method returns self for method chaining', function () {
    $rule = ErrorHandlingRule::allowFailures(5);
    $result = $this->ruleset->addRule($rule);

    expect($result)->toBe($this->ruleset);
});

test('addRules method adds multiple rules', function () {
    $rule1 = ErrorHandlingRule::allowFailures(5);
    $rule2 = ErrorHandlingRule::allowFailures(10);
    $this->ruleset->addRules([$rule1, $rule2]);

    expect($this->ruleset->getRules())
        ->toBeArray()
        ->toHaveCount(2)
        ->toContain($rule1, $rule2);
});

test('addRules method returns self for method chaining', function () {
    $rules = [
        ErrorHandlingRule::allowFailures(5),
        ErrorHandlingRule::allowFailures(10),
    ];
    $result = $this->ruleset->addRules($rules);

    expect($result)->toBe($this->ruleset);
});

test('addRules method handles empty array', function () {
    $this->ruleset->addRules([]);

    expect($this->ruleset->getRules())->toBeArray()->toBeEmpty();
});

test('rules are added in the correct order', function () {
    $rule1 = ErrorHandlingRule::allowFailures(5);
    $rule2 = ErrorHandlingRule::allowFailures(10);
    $rule3 = ErrorHandlingRule::allowFailures(15);

    $this->ruleset->addRule($rule1)->addRules([$rule2, $rule3]);

    expect($this->ruleset->getRules())->toBe([$rule1, $rule2, $rule3]);
});
