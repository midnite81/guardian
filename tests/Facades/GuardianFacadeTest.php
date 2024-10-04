<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Midnite81\Guardian\Facades\Guardian;
use Midnite81\Guardian\Factories\LaravelGuardianFactory;

uses(\Midnite81\Guardian\Tests\OrchestraTestCase::class)->group('facades');

beforeEach(function () {
    $this->app->bind(LaravelGuardianFactory::class, function () {
        return new LaravelGuardianFactory;
    });
});

it('can be used as a facade', function () {
    $result = Guardian::make('unique-identifier');

    expect($result)->toBeInstanceOf(\Midnite81\Guardian\Guardian::class);
});

it('is bound in the service container', function () {
    expect(App::bound(LaravelGuardianFactory::class))->toBeTrue();
});
