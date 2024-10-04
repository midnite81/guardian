<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Facades;

use Illuminate\Support\Facades\Facade;
use Midnite81\Guardian\Factories\LaravelGuardianFactory;

/**
 * @method static \Midnite81\Guardian\Guardian make(array $config = [])
 *
 * @see \Midnite81\Guardian\Factories\LaravelGuardianFactory
 */
class Guardian extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return LaravelGuardianFactory::class;
    }
}
