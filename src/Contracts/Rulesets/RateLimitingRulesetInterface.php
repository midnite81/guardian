<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Contracts\Rulesets;

use Exception;
use Midnite81\Guardian\Rules\RateLimitRule;

interface RateLimitingRulesetInterface
{
    /**
     * Retrieves the list of all rate limiting rules.
     *
     * @return array<int, RateLimitRule> An array of RateLimitRule objects.
     */
    public function rules(): array;

    /**
     * Retrieves the set of rules defined in the current context.
     *
     * @return array<int, RateLimitRule> The array of rules.
     */
    public function getRules(): array;

    /**
     * Adds a single rate limiting rule to the collection of rules.
     *
     * @param RateLimitRule $rule The rule to be added
     * @return self Returns the instance of the object for method chaining
     */
    public function addRule(RateLimitRule $rule): self;

    /**
     * Adds a set of rules to the rules property.
     *
     * @param array<int, RateLimitRule> $rules An array of rules to be added
     * @return self Returns the instance of the object for method chaining
     *
     * @throws Exception If any item in the array is not an instance of RateLimitRule
     */
    public function addRules(array $rules): self;
}
