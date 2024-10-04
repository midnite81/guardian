<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Factories;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Exceptions\IdentifierCannotBeEmptyException;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Store\LaravelStore;

class LaravelGuardianFactory
{
    /**
     * Constructs and configures a Guardian instance with the specified identifier, rules, and error rules.
     *
     * This static method creates a Guardian instance and configures it with the provided
     * identifier, cache store, rate limiting rules, and error handling rules.
     *
     * @param string $identifier A unique identifier for this group of requests.
     * @param RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules The rate limiting rules to apply.
     *                                                                           If an array is provided, it should contain
     *                                                                           RateLimitRule instances.
     * @param ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules The error handling rules to
     *                                                                                     apply. If an array is provided, it should contain ErrorHandlingRule instances.
     * @param string $cachePrefix The prefix to use for cache keys. Defaults to 'guardian'.
     * @return Guardian A fully configured Guardian instance ready for use within a Laravel application.
     *
     * @throws BindingResolutionException If there's an issue resolving dependencies from the container.
     * @throws IdentifierCannotBeEmptyException If the provided identifier is empty.
     * @throws Exception
     */
    public static function make(
        string $identifier,
        RateLimitingRulesetInterface|array|null $rules = null,
        ErrorHandlingRulesetInterface|array|null $errorRules = null,
        string $cachePrefix = 'guardian'
    ): Guardian {
        /** @var LaravelStore $cache */
        $cache = app()->make(LaravelStore::class);

        return new Guardian($identifier, $cache, $rules, $errorRules, $cachePrefix);
    }

    /**
     * Creates and configures a Guardian instance for Laravel applications with custom parameters.
     *
     * This static factory method creates a Guardian instance with the provided identifier,
     * and configures it with the specified cache, rules, and error rules.
     *
     * @param string $identifier A unique identifier for this group of requests.
     * @param CacheInterface|null $cache The cache implementation to use. If null, LaravelStore will be used.
     * @param RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules The rate limiting rules to apply.
     *                                                                           If an array is provided, it should contain
     *                                                                           RateLimitRule instances.
     * @param ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules The error handling rules to
     *                                                                                     apply. If an array is provided, it should contain ErrorHandlingRule instances.
     * @return Guardian A fully configured Guardian instance ready for use within a Laravel application.
     *
     * @throws BindingResolutionException If there's an issue resolving dependencies from the container.
     * @throws IdentifierCannotBeEmptyException If the provided identifier is empty.
     * @throws Exception
     *
     * @example
     * $guardian = LaravelGuardianFactory::create('api-gateway', $redisCache, $apiRules, $customErrorRule);
     */
    public static function create(
        string $identifier,
        ?CacheInterface $cache = null,
        RateLimitingRulesetInterface|array|null $rules = null,
        ErrorHandlingRulesetInterface|array|null $errorRules = null,
    ): Guardian {
        return new Guardian(
            $identifier,
            /* @phpstan-ignore-next-line */
            $cache ?? app()->make(LaravelStore::class),
            $rules,
            $errorRules,
        );
    }
}
