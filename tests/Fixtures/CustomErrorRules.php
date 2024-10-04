<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Tests\Fixtures;

use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rulesets\AbstractErrorHandlingRuleset;

class CustomErrorRules extends AbstractErrorHandlingRuleset
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            ErrorHandlingRule::allowFailures(1)->perHour(),
        ];
    }
}
