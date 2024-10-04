<?php

declare(strict_types=1);

use Illuminate\Cache\Repository;
use Midnite81\Guardian\Store\LaravelStore;

uses()->group('stores');

beforeEach(function () {
    $this->mockCache = Mockery::mock(Repository::class);
    $this->store = new LaravelStore($this->mockCache);
});

afterEach(function () {
    Mockery::close();
});

test('get method calls cache repository get method', function () {
    $this->mockCache->shouldReceive('get')
        ->once()
        ->with('test_key', null)
        ->andReturn('test_value');

    $result = $this->store->get('test_key');

    expect($result)->toBe('test_value');
});

test('has method calls cache repository has method', function () {
    $this->mockCache->shouldReceive('has')
        ->once()
        ->with('test_key')
        ->andReturn(true);

    $result = $this->store->has('test_key');

    expect($result)->toBeTrue();
});

test('put method calls cache repository put method with null ttl', function () {
    $this->mockCache->shouldReceive('put')
        ->once()
        ->with('test_key', 'test_value', null)
        ->andReturn(true);

    $result = $this->store->put('test_key', 'test_value');

    expect($result)->toBeTrue();
});

test('put method calls cache repository put method with integer ttl', function () {
    $this->mockCache->shouldReceive('put')
        ->once()
        ->with('test_key', 'test_value', 3600)
        ->andReturn(true);

    $result = $this->store->put('test_key', 'test_value', 3600);

    expect($result)->toBeTrue();
});

test('put method calls cache repository put method with DateInterval ttl', function () {
    $interval = new DateInterval('PT1H');

    $this->mockCache->shouldReceive('put')
        ->once()
        ->with('test_key', 'test_value', $interval)
        ->andReturn(true);

    $result = $this->store->put('test_key', 'test_value', $interval);

    expect($result)->toBeTrue();
});

test('put method calls cache repository put method with DateTime ttl', function () {
    $dateTime = new DateTime('+1 hour');

    $this->mockCache->shouldReceive('put')
        ->once()
        ->with('test_key', 'test_value', $dateTime)
        ->andReturn(true);

    $result = $this->store->put('test_key', 'test_value', $dateTime);

    expect($result)->toBeTrue();
});

test('forget method calls cache repository forget method', function () {
    $this->mockCache->shouldReceive('forget')
        ->once()
        ->with('test_key')
        ->andReturn(true);

    $result = $this->store->forget('test_key');

    expect($result)->toBeTrue();
});
