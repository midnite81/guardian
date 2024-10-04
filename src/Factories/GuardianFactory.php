<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Factories;

use Exception;
use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Exceptions\IdentifierCannotBeEmptyException;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;

class GuardianFactory
{
    /**
     * Creates a new Guardian instance with optional caching, rate limiting, and error handling rules.
     *
     * @param string $identifier The unique identifier for the Guardian instance.
     * @param CacheInterface $cache The cache implementation to use.
     * @param RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules The rate limiting rules to apply.
     * @param ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules The error handling rules to apply.
     * @return Guardian The newly created and configured Guardian instance.
     *
     * @throws IdentifierCannotBeEmptyException If the provided identifier is empty.
     * @throws Exception If there's an error during Guardian instantiation.
     */
    public static function create(
        string $identifier,
        CacheInterface $cache,
        RateLimitingRulesetInterface|array|null $rules = null,
        ErrorHandlingRulesetInterface|array|null $errorRules = null
    ): Guardian {
        return new Guardian($identifier, $cache, $rules, $errorRules);
    }
}
