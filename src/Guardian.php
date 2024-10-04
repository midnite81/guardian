<?php

declare(strict_types=1);

namespace Midnite81\Guardian;

use Closure;
use DateMalformedStringException;
use Exception;
use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Exceptions\IdentifierCannotBeEmptyException;
use Midnite81\Guardian\Exceptions\RulePreventsExecutionException;
use Midnite81\Guardian\Helpers\Arrays;
use Midnite81\Guardian\Helpers\RulesetPreparator;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Rulesets\GenericErrorHandlingRuleset;
use Midnite81\Guardian\Rulesets\GenericRateLimitingRuleset;

class Guardian
{
    /**
     * @var string The unique identifier for this Guardian instance
     */
    protected string $identifier;

    /**
     * @var RateLimitingRulesetInterface|null The rate limiting rules
     */
    protected ?RateLimitingRulesetInterface $rules = null;

    /**
     * @var ErrorHandlingRulesetInterface|null The error handling rules
     */
    protected ?ErrorHandlingRulesetInterface $errorRules = null;

    /**
     * @var RateLimitRule|null The rule that prevented execution
     */
    protected ?RateLimitRule $preventingRule = null;

    /**
     * Initializes a new instance of the Guardian class with the given parameters.
     *
     * @param string $identifier A unique identifier for the instance
     * @param CacheInterface $cache An implementation of cache storage
     * @param RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules The rate limiting rules to apply
     * @param ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules The error handling rules to apply
     * @param string $cachePrefix The prefix to use for cache keys
     *
     * @throws IdentifierCannotBeEmptyException
     * @throws Exception
     */
    public function __construct(
        string $identifier,
        protected CacheInterface $cache,
        RateLimitingRulesetInterface|array|null $rules = null,
        ErrorHandlingRulesetInterface|array|null $errorRules = null,
        protected string $cachePrefix = 'guardian',
    ) {
        $this->rules = $rules !== null ? RulesetPreparator::prepareRules($rules) : null;
        $this->errorRules = $errorRules !== null ? RulesetPreparator::prepareErrorRules($errorRules) : null;

        $this->setIdentifier($identifier, $this->cachePrefix);
    }

    /**
     * Executes the given closure if the request can be run based on current rules.
     *
     * @param Closure $request The closure to be executed
     * @param bool $throwIfRulePrevents Determine if an exception should be thrown when a rule prevents execution
     * @return mixed The result of the closure execution, or null if execution was prevented by a rule
     *
     * @throws RulePreventsExecutionException
     * @throws DateMalformedStringException
     */
    public function send(Closure $request, bool $throwIfRulePrevents = true): mixed
    {
        $this->preventingRule = null;

        if ($this->canRun()) {
            try {
                $result = $request();
                $this->incrementRateLimitCounters();

                return $result;
            } catch (Exception $e) {
                if ($this->shouldThrow()) {
                    throw $e;
                } else {
                    return null;
                }
            }
        }

        if ($throwIfRulePrevents) {
            throw new RulePreventsExecutionException($this->preventingRule);
        }

        return null;
    }

    /**
     * Sets the identifier with a given prefix, ensuring it is sanitized and has a valid format.
     *
     * @param string $identifier The base string for the identifier, which will be sanitized
     * @param string $prefix The prefix to prepend to the identifier
     * @return void
     *
     * @throws IdentifierCannotBeEmptyException
     */
    public function setIdentifier(string $identifier, string $prefix = 'guardian'): void
    {
        if (empty($identifier)) {
            throw new IdentifierCannotBeEmptyException('Identifier cannot be empty');
        }

        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier);

        if (empty($prefix)) {
            if (!preg_match('/^[a-zA-Z]/', $safe ?? '')) {
                $safe = 'id_' . $safe;
            }
        }

        if ($safe === null || $safe === 'id_') {
            throw new IdentifierCannotBeEmptyException('Identifier cannot be empty');
        }

        $this->identifier = ($prefix ? $prefix . '_' : '') . substr($safe, 0, 128);
    }

    /**
     * Sets the rate limiting rules.
     *
     * @param RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules The rate limiting rules to apply
     * @return self Returns the current instance for method chaining
     *
     * @throws Exception
     */
    public function setRules(RateLimitingRulesetInterface|array|null $rules = null): self
    {
        $this->rules = $rules !== null ? RulesetPreparator::prepareRules($rules) : null;

        return $this;
    }

    /**
     * Sets the error handling rules.
     *
     * @param ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules The error handling rules to apply
     * @return self Returns the current instance for method chaining
     *
     * @throws Exception
     */
    public function setErrorRules(ErrorHandlingRulesetInterface|array|null $errorRules = null): self
    {
        $this->errorRules = $errorRules !== null ? RulesetPreparator::prepareErrorRules($errorRules) : null;

        return $this;
    }

    /**
     * Adds a set of rules to the GenericRuleset.
     *
     * @param array<int, RateLimitRule> $rules An array of rules to be added
     * @return static The instance with the updated rules
     *
     * @throws Exception
     */
    public function addRules(array $rules): static
    {
        Arrays::mustBeInstanceOf($rules, RateLimitRule::class);

        if ($this->rules instanceof RateLimitingRulesetInterface) {
            $this->rules->addRules($rules);

            return $this;
        }

        $this->rules = (new GenericRateLimitingRuleset)->addRules($rules);

        return $this;
    }

    /**
     * Adds error handling rules to the current set of error rules.
     *
     * @param array<int, ErrorHandlingRule> $rules An array of rules to be added
     * @return static The current instance for method chaining
     *
     * @throws Exception
     */
    public function addErrorRules(array $rules): static
    {
        Arrays::mustBeInstanceOf($rules, ErrorHandlingRule::class);

        if ($this->errorRules) {
            $this->errorRules = $this->errorRules->addRules($rules);

            return $this;
        }

        $this->errorRules = (new GenericErrorHandlingRuleset)->addRules($rules);

        return $this;
    }

    /**
     * Retrieves the current rate limiting rules.
     *
     * @return RateLimitingRulesetInterface|null The rate limiting rules, or null if no rules are set
     */
    public function getRules(): ?RateLimitingRulesetInterface
    {
        return $this->rules;
    }

    /**
     * Retrieves the current cache instance.
     *
     * @return CacheInterface The cache instance
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * Sets the cache.
     *
     * @param CacheInterface $cache The cache instance to set
     * @return Guardian The current instance of the Guardian class
     */
    public function setCache(CacheInterface $cache): Guardian
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Retrieves the current error handling rules.
     *
     * @return ErrorHandlingRulesetInterface|null The error handling rules
     */
    public function getErrorRules(): ?ErrorHandlingRulesetInterface
    {
        return $this->errorRules;
    }

    /**
     * Retrieves the identifier.
     *
     * @return string The identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Generates a cache key for a given rule.
     *
     * @param RateLimitRule $rule The rule for which to generate a cache key
     * @return string The generated cache key
     */
    public function getCacheKey(RateLimitRule $rule): string
    {
        return $rule->getKey($this->identifier . ':');
    }

    /**
     * Clears all cache entries associated with this Guardian instance.
     *
     * @return bool True if the cache was successfully cleared, false otherwise
     */
    public function clearCache(): bool
    {
        $prefix = $this->identifier . ':';
        $keysToDelete = [];

        // Collect all keys with the current identifier prefix
        if ($this->rules) {
            foreach ($this->rules->getRules() as $rule) {
                $keysToDelete[] = $rule->getKey($prefix);
            }
        }

        if ($this->errorRules) {
            foreach ($this->errorRules->getRules() as $rule) {
                $keysToDelete[] = $rule->getKey($prefix . 'error:');
            }
        }

        // Delete all collected keys
        $allDeleted = true;
        foreach ($keysToDelete as $key) {
            if (!$this->cache->forget($key)) {
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }

    /**
     * Determines whether the task can be executed based on the defined rules.
     *
     * @return bool True if the task can run, false otherwise
     */
    protected function canRun(): bool
    {
        if (!$this->rules) {
            return true;
        }

        foreach ($this->rules->getRules() as $rule) {
            if (!$this->checkRule($rule)) {
                $this->preventingRule = $rule;

                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a specific rule allows execution.
     *
     * @param RateLimitRule $rule The rule to check
     * @return bool True if the rule allows execution, false otherwise
     */
    protected function checkRule(RateLimitRule $rule): bool
    {
        $key = $this->getCacheKey($rule);
        $count = $this->cache->get($key, 0);

        return $count < $rule->getLimit();
    }

    /**
     * Increments the rate limit counters for each rule in the current rate limiting configuration.
     *
     * @return void
     */
    protected function incrementRateLimitCounters(): void
    {
        if (!$this->rules) {
            return;
        }

        foreach ($this->rules->getRules() as $rule) {
            $key = $this->getCacheKey($rule);
            $count = $this->cache->get($key, 0);
            $newCount = $count + 1;
            $this->cache->put($key, $newCount, $rule->getTotalSeconds());
        }
    }

    /**
     * Determines if an exception should be thrown based on error handling rules.
     *
     * @return bool True if an exception should be thrown, otherwise false
     */
    protected function shouldThrow(): bool
    {
        if (!$this->errorRules) {
            return true; // Default behavior if no error rules are set
        }

        foreach ($this->errorRules->getRules() as $rule) {
            if ($this->checkErrorRule($rule)) {
                return $rule->shouldThrow();
            }
        }

        return false; // If no rules match, don't throw
    }

    /**
     * Checks if an error rule has been triggered.
     *
     * @param ErrorHandlingRule $rule The error handling rule to check
     * @return bool True if the rule has been triggered, false otherwise*
     */
    protected function checkErrorRule(ErrorHandlingRule $rule): bool
    {
        $key = $rule->getKey($this->identifier . ':error:');
        $count = $this->cache->get($key, 0);

        if ($count >= $rule->getFailureThreshold()) {
            return true;
        }

        $this->cache->put($key, $count + 1, $rule->getTotalSeconds());

        return false;
    }
}
