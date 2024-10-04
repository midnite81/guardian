<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Rulesets;

/**
 * Class GenericErrorHandlingRuleset
 *
 * Implements the ErrorHandlingRulesetInterface and manages a collection of error handling rules.
 */
class GenericErrorHandlingRuleset extends AbstractErrorHandlingRuleset
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [];
    }
}
