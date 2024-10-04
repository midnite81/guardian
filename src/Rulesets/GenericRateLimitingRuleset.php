<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Rulesets;

/**
 * Class GenericRateLimitingRuleset
 *
 * Implements the RateLimitingRulesetInterface and manages a collection of rate limiting rules.
 */
class GenericRateLimitingRuleset extends AbstractRateLimitingRuleset
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [];
    }
}
