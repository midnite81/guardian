<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Rulesets;

use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Rules\ErrorHandlingRule;

abstract class AbstractErrorHandlingRuleset implements ErrorHandlingRulesetInterface
{
    /**
     * @var array<int, ErrorHandlingRule> The collection of error handling rules
     */
    protected array $rules = [];

    /**
     * Constructor method to initialize the rules property by invoking the rules method.
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
    public function addRule(ErrorHandlingRule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addRules(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }
}
