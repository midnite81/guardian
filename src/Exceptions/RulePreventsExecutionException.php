<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Exceptions;

use Exception;
use Midnite81\Guardian\Rules\RateLimitRule;

/**
 * Exception thrown when a rule prevents execution of a request.
 */
class RulePreventsExecutionException extends Exception
{
    /**
     * The rule that prevented execution, if any.
     */
    protected ?RateLimitRule $preventingRule;

    /**
     * Constructs a new RulePreventsExecutionException.
     *
     * @param RateLimitRule|null $preventingRule The rule that prevented execution, or null if no specific rule.
     */
    public function __construct(?RateLimitRule $preventingRule = null)
    {
        $this->preventingRule = $preventingRule;
        $message = $this->getErrorMessageFromRule();
        parent::__construct($message);
    }

    /**
     * Gets the rule that prevented execution.
     *
     * @return RateLimitRule|null The preventing rule, or null if no specific rule.
     */
    public function getPreventingRule(): ?RateLimitRule
    {
        return $this->preventingRule;
    }

    /**
     * Generates an error message based on the preventing rule.
     *
     * @return string The generated error message.
     */
    protected function getErrorMessageFromRule(): string
    {
        if (!$this->preventingRule) {
            return 'Cannot execute the request because a rule prevents it.';
        }

        return sprintf(
            'Cannot execute the request. Rate limit exceeded: %d requests per %s %s.',
            $this->preventingRule->getLimit(),
            $this->preventingRule->getDuration(),
            $this->preventingRule->getInterval()->value
        );
    }
}
