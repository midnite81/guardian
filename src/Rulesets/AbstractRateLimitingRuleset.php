<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Rulesets;

use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Helpers\Arrays;
use Midnite81\Guardian\Rules\RateLimitRule;

abstract class AbstractRateLimitingRuleset implements RateLimitingRulesetInterface
{
    /**
     * @var array<int, RateLimitRule>
     */
    protected array $rules = [];

    /**
     * Initializes the constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rules = $this->rules();
    }

    /**
     * {@inheritDoc}
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * {@inheritDoc}
     */
    public function addRules(array $rules): self
    {
        Arrays::mustBeInstanceOf($rules, RateLimitRule::class);

        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addRule(RateLimitRule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }
}
