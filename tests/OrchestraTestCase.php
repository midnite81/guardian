<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Tests;

use Midnite81\Guardian\Providers\GuardianServiceProvider;
use Orchestra\Testbench\TestCase;

class OrchestraTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            GuardianServiceProvider::class,
        ];
    }
}
