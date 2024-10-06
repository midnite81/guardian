# Guardian

Guardian is a powerful and flexible rate limiting and error handling library for PHP applications. It provides a robust
system for managing API rate limits, preventing abuse, and handling errors gracefully. Guardian can be used in any PHP
project and has built-in support for Laravel applications. Full
documentation: [https://guardian.midnite.uk](https://guardian.midnite.uk)

## Requirements

- PHP 8.2 or higher

## Key Features

- Flexible rate limiting rules
- Configurable error handling
- Multiple cache drivers (File, Redis, Laravel)
- Ability to add custom drivers
- Easy integration with Laravel
- Customizable for any PHP project

## Installation

Install Guardian via Composer:

```bash
composer require midnite81/guardian
```

## Quick Start

```php
use Midnite81\Guardian\Factories\GuardianFactory;
use Midnite81\Guardian\Store\FileStore;
use Midnite81\Guardian\Rules\RateLimitRule;

$guardian = GuardianFactory::create(
    'my-api',
    new FileStore('/path/to/cache'),
    [RateLimitRule::allow(100)->perMinute()]
);

$result = $guardian->send(function() {
    // Your API logic here
});
```

## Documentation

For detailed usage instructions, API reference, and advanced configuration options, please refer to the official
Guardian documentation:

[https://guardian.midnite.uk](https://guardian.midnite.uk)

## Future plans for Guardian

As of October 2024, Guardian has just been released and there is no roadmap as such for it as yet. Its use in the wild
might bring in required features.

Some initial thoughts:

- More integration for Laravel, but introducing a config file to make getting instances more easily
- Adding in a way to work with PSR-7, PSR-18, PSR-17, and PSR-15

## Contributing

Contributions are welcome! Please see our [Contributing Guide](CONTRIBUTING.md) for more details.

## License

Guardian is open-sourced software licensed under the [MIT license](LICENSE).