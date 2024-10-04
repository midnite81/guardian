<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Tests\Fixtures;

use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Rulesets\AbstractRateLimitingRuleset;

class CustomRules extends AbstractRateLimitingRuleset
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            RateLimitRule::allow(10)->perMinute(),
        ];
    }
}
