<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Helpers;

use Exception;
use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Rulesets\GenericErrorHandlingRuleset;
use Midnite81\Guardian\Rulesets\GenericRateLimitingRuleset;

/**
 * Helper class for preparing rulesets.
 */
class RulesetPreparator
{
    /**
     * Prepare the rate limiting rules based on the provided input.
     *
     * @param RateLimitingRulesetInterface|array<int, RateLimitRule> $rules
     * @return RateLimitingRulesetInterface|null
     *
     * @throws Exception If the rules are not of the correct type.
     */
    public static function prepareRules(RateLimitingRulesetInterface|array $rules): ?RateLimitingRulesetInterface
    {
        if ($rules instanceof RateLimitingRulesetInterface) {
            return $rules;
        }

        Arrays::mustBeInstanceOf($rules, RateLimitRule::class);

        return !empty($rules) ? (new GenericRateLimitingRuleset)->addRules($rules) : null;
    }

    /**
     * Prepares and returns an error handling ruleset based on the provided input.
     *
     * @param ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule> $errorRules
     * @return ErrorHandlingRulesetInterface|null
     *
     * @throws Exception If the error rules are not of the correct type.
     */
    public static function prepareErrorRules(
        ErrorHandlingRulesetInterface|array $errorRules
    ): ?ErrorHandlingRulesetInterface {
        if ($errorRules instanceof ErrorHandlingRulesetInterface) {
            return $errorRules;
        }

        Arrays::mustBeInstanceOf($errorRules, ErrorHandlingRule::class);

        return !empty($errorRules) ? (new GenericErrorHandlingRuleset)->addRules($errorRules) : null;
    }
}
