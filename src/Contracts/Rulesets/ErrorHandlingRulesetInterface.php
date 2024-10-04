<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Contracts\Rulesets;

use Midnite81\Guardian\Rules\ErrorHandlingRule;

interface ErrorHandlingRulesetInterface
{
    /**
     * Defined rules for the ruleset
     *
     * @return array<int, ErrorHandlingRule> An array of ErrorHandlingRule objects.
     */
    public function rules(): array;

    /**
     * Retrieves the set of rules defined in the current context.
     *
     * @return array<int, ErrorHandlingRule> The array of rules.
     */
    public function getRules(): array;

    /**
     * Adds an error handling rule to the collection of rules.
     *
     * @param ErrorHandlingRule $rule The rule to be added
     * @return $this Returns the instance of the object for method chaining
     */
    public function addRule(ErrorHandlingRule $rule): self;

    /**
     * Adds multiple error handling rules to the collection of rules.
     *
     * @param array<int, ErrorHandlingRule> $rules An array of rules to be added
     * @return $this Returns the instance of the object for method chaining
     */
    public function addRules(array $rules): self;
}
