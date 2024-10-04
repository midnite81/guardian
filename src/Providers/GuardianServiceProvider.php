<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Store\LaravelStore;

/**
 * GuardianServiceProvider class
 *
 * This service provider registers the Guardian service and factory within the application.
 */
class GuardianServiceProvider extends ServiceProvider
{
    /**
     * Register the Guardian service and factory within the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerGuardian();
        $this->registerGuardianFactory();
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        // No initialization needed
    }

    /**
     * Register the Guardian service.
     *
     * @return void
     */
    protected function registerGuardian(): void
    {
        $this->app->bind(Guardian::class, function ($app, $parameters) {
            return new Guardian(
                $parameters[0] ?? 'laravel-guardian',
                new LaravelStore($app['cache.store'])
            );
        });
    }

    /**
     * Register the Guardian factory.
     *
     * @return void
     */
    protected function registerGuardianFactory(): void
    {
        $this->app->bind('guardian.factory', function ($app) {
            return new class($app)
            {
                /**
                 * @var Application The application instance.
                 */
                private Application $app;

                /**
                 * Constructor for the anonymous class.
                 *
                 * @param Application $app The application instance.
                 */
                public function __construct(Application $app)
                {
                    $this->app = $app;
                }

                /**
                 * Creates a Guardian instance, sets its identifier and optional rules.
                 *
                 * @param string $identifier The identifier for the Guardian instance.
                 * @param array<int, RateLimitRule> $rules An array of rules to be added to the Guardian.
                 * @param array<int, ErrorHandlingRule>|null $errorRules An optional array of error rules to be added to the Guardian.
                 * @return Guardian The configured Guardian instance.
                 */
                public function make(string $identifier, array $rules = [], ?array $errorRules = null): Guardian
                {
                    /** @var Guardian $guardian */
                    $guardian = $this->app->make(Guardian::class, [$identifier]);

                    if (!empty($rules)) {
                        $guardian->addRules($rules);
                    }

                    if ($errorRules !== null) {
                        $guardian->addErrorRules($errorRules);
                    }

                    return $guardian;
                }
            };
        });
    }
}
