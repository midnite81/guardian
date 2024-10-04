# Guardian

Guardian is a powerful and flexible rate limiting and error handling library for PHP applications. It provides a robust system for managing API rate limits, preventing abuse, and handling errors gracefully. Guardian can be used in any PHP project and has built-in support for Laravel applications.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Creating a Guardian Instance](#creating-a-guardian-instance)
    - [Using Factories (Recommended)](#using-factories-recommended)
    - [Direct Class Instantiation](#direct-class-instantiation)
    - [Dependency Injection (Laravel)](#dependency-injection-laravel)
    - [Facade (Laravel)](#facade-laravel)
  - [Cache Drivers](#cache-drivers)
    - [Available Cache Drivers](#available-cache-drivers)
    - [Creating Custom Cache Drivers](#creating-custom-cache-drivers)
  - [Rate Limiting Rules](#rate-limiting-rules)
    - [Creating Rate Limit Rules](#creating-rate-limit-rules)
    - [Creating Custom Rulesets](#creating-custom-rulesets)
  - [Error Handling Rules](#error-handling-rules)
    - [Creating Error Handling Rules](#creating-error-handling-rules)
    - [Creating Custom Error Handling Rulesets](#creating-custom-error-handling-rulesets)
- [API Reference](#api-reference)
  - [Guardian Class](#guardian-class)
  - [RateLimitRule Class](#ratelimitrule-class)
  - [ErrorHandlingRule Class](#errorhandlingrule-class)
- [Contributing](#contributing)
- [License](#license)

## Installation

You can install Guardian via Composer:

```bash
composer require midnite81/guardian
```

If you're using Laravel, the service provider will be automatically registered.

## Usage

### Creating a Guardian Instance

There are several ways to create a Guardian instance, depending on your project setup and preferences.

#### Using Factories (Recommended)

The factory method is the recommended way to create a Guardian instance as it provides a clean and flexible API.

For non-Laravel projects:

```php
use Midnite81\Guardian\Factories\GuardianFactory;
use Midnite81\Guardian\Store\FileStore;

$guardian = GuardianFactory::create(
    'my-api',
    new FileStore('/path/to/cache'),
    [RateLimitRule::allow(100)->perMinute()],
    [ErrorHandlingRule::allowFailures(5)->perMinute()]
);
```

For Laravel projects:

```php
use Midnite81\Guardian\Facades\Guardian;

$guardian = Guardian::make(
    'my-api',
    [RateLimitRule::allow(100)->perMinute()],
    [ErrorHandlingRule::allowFailures(5)->perMinute()]
);
```

#### Direct Class Instantiation

You can also create a Guardian instance directly:

```php
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Store\FileStore;

$guardian = new Guardian(
    'my-api',
    new FileStore('/path/to/cache'),
    [RateLimitRule::allow(100)->perMinute()],
    [ErrorHandlingRule::allowFailures(5)->perMinute()]
);
```

#### Dependency Injection (Laravel)

In Laravel applications, you can use dependency injection to get a Guardian instance:

```php
use Midnite81\Guardian\Guardian;

class MyController
{
    public function __construct(private Guardian $guardian)
    {
        $this->guardian->setIdentifier('my-api');
        $this->guardian->addRules([RateLimitRule::allow(100)->perMinute()]);
        $this->guardian->addErrorRules([ErrorHandlingRule::allowFailures(5)->perMinute()]);
    }
}
```

#### Facade (Laravel)

Laravel users can also use the Guardian facade:

```php
use Midnite81\Guardian\Facades\Guardian;

$guardian = Guardian::make(
    'weather-conditions-api', 
    [RateLimitRule::allow(100)->perMinute()]
);
```

### Cache Drivers

Guardian uses cache drivers to store rate limiting and error handling data. If you're using the agnostic version of Guardian, you need to provide a cache driver that implements the `CacheInterface`.

#### Available Cache Drivers

Guardian comes with the following built-in cache drivers:

- `FileStore`: Stores data in files on the local filesystem.
- `RedisStore`: Stores data in Redis.
- `LaravelStore`: Uses Laravel's cache system (automatically used in Laravel applications).

#### Creating Custom Cache Drivers

You can create your own cache driver by implementing the `CacheInterface`:

```php
use Midnite81\Guardian\Contracts\Store\CacheInterface;

class MyCustomCacheDriver implements CacheInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        // Implementation
    }

    public function has(string $key): bool
    {
        // Implementation
    }

    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): bool
    {
        // Implementation
    }

    public function forget(string $key): bool
    {
        // Implementation
    }
}
```

### Rate Limiting Rules

Rate limiting rules define how many requests are allowed within a given time frame.

#### Creating Rate Limit Rules

You can create rate limit rules using the `RateLimitRule` class:

```php
use Midnite81\Guardian\Rules\RateLimitRule;

$rule = RateLimitRule::allow(100)->perMinute();
$rule = RateLimitRule::allow(1000)->perHour();
$rule = RateLimitRule::allow(10000)->perDay();
```

#### Creating Custom Rulesets

You can create custom rulesets by implementing the `RateLimitingRulesetInterface` and extending the `AbstractRateLimitingRuleset`:

```php
use Midnite81\Guardian\Contracts\Rulesets\RateLimitingRulesetInterface;
use Midnite81\Guardian\Rulesets\AbstractRateLimitingRuleset;
use Midnite81\Guardian\Rules\RateLimitRule;

class MyCustomRuleset extends AbstractRateLimitingRuleset implements RateLimitingRulesetInterface
{
    public function rules(): array
    {
        return [
            RateLimitRule::allow(100)->perMinute(),
            RateLimitRule::allow(1000)->perHour(),
        ];
    }
}
```

### Error Handling Rules

Error handling rules define how many errors are allowed before taking action.

#### Creating Error Handling Rules

You can create error handling rules using the `ErrorHandlingRule` class:

```php
use Midnite81\Guardian\Rules\ErrorHandlingRule;

$rule = ErrorHandlingRule::allowFailures(5)->perMinute();
$rule = ErrorHandlingRule::allowFailures(50)->perHour()->thenThrow(false);
```

#### Creating Custom Error Handling Rulesets

You can create custom error handling rulesets by implementing the `ErrorHandlingRulesetInterface` and extending the `AbstractErrorHandlingRuleset`:

```php
use Midnite81\Guardian\Contracts\Rulesets\ErrorHandlingRulesetInterface;
use Midnite81\Guardian\Rulesets\AbstractErrorHandlingRuleset;
use Midnite81\Guardian\Rules\ErrorHandlingRule;

class MyCustomErrorRuleset extends AbstractErrorHandlingRuleset implements ErrorHandlingRulesetInterface
{
    public function rules(): array
    {
        return [
            ErrorHandlingRule::allowFailures(5)->perMinute(),
            ErrorHandlingRule::allowFailures(50)->perHour()->thenThrow(false),
        ];
    }
}
```

## API Reference

### Guardian Class

The main class for managing rate limiting and error handling.

```php
class Guardian
{
    public function __construct(string $identifier, CacheInterface $cache, RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules = null, ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules = null, string $cachePrefix = 'guardian');
    public function send(Closure $request, bool $throwIfRulePrevents = true): mixed;
    public function setIdentifier(string $identifier, string $prefix = 'guardian'): void;
    public function setRules(RateLimitingRulesetInterface|array<int, RateLimitRule>|null $rules = null): self;
    public function setErrorRules(ErrorHandlingRulesetInterface|array<int, ErrorHandlingRule>|null $errorRules = null): self;
    public function addRules(array<int, RateLimitRule> $rules): static;
    public function addErrorRules(array<int, ErrorHandlingRule> $rules): static;
    public function getRules(): ?RateLimitingRulesetInterface;
    public function getCache(): CacheInterface;
    public function setCache(CacheInterface $cache): Guardian;
    public function getErrorRules(): ?ErrorHandlingRulesetInterface;
    public function getIdentifier(): string;
    public function getCacheKey(RateLimitRule $rule): string;
    public function clearCache(): bool;
}
```

### RateLimitRule Class

Defines rate limiting rules.

```php
class RateLimitRule
{
    public static function allow(int $limit): self;
    public function every(int $amount, Interval $unit): self;
    public function perSecond(): self;
    public function perSeconds(int $seconds): self;
    public function perMinute(): self;
    public function perMinutes(int $minutes): self;
    public function perHour(): self;
    public function perHours(int $hours): self;
    public function perDay(): self;
    public function perDays(int $days): self;
    public function perWeek(): self;
    public function perWeeks(int $weeks): self;
    public function perMonth(): self;
    public function perMonths(int $months): self;
    public function dailyUntil(string $time): self;
    public function untilMidnightTonight(): self;
    public function untilEndOfMonth(): self;
    public function getLimit(): int;
    public function getInterval(): Interval;
    public function getDuration(): int;
    public function getUntil(): ?DateTimeImmutable;
    public function getTotalSeconds(): int;
    public function getKey(string $prefix = '', string $suffix = ''): string;
}
```

### ErrorHandlingRule Class

Defines error handling rules.

```php
class ErrorHandlingRule
{
    public static function allowFailures(int $failureThreshold): self;
    public function perInterval(Interval $interval, int $duration = 1): self;
    public function perMinute(): self;
    public function perMinutes(int $value): self;
    public function perHour(): self;
    public function perHours(int $value): self;
    public function perDay(): self;
    public function perDays(int $value): self;
    public function untilMidnightTonight(): self;
    public function thenThrow(bool $shouldThrow = true): self;
    public function getFailureThreshold(): int;
    public function getInterval(): ?Interval;
    public function getDuration(): int;
    public function getUntil(): ?DateTimeImmutable;
    public function shouldThrow(): bool;
    public function getTotalSeconds(): int;
    public function getKey(string $prefix = '', string $suffix = ''): string;
}
```

## Contributing

Contributions are welcome from everyone, whether it's a bug report, feature suggestion, documentation improvement,
or a code contribution. Please see the [contributing page](CONTRIBUTING.md) for more information.

## License

This project is licensed under the MIT License.