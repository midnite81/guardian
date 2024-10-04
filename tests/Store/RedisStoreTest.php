<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Midnite81\Guardian\Exceptions\Store\RedisStoreException;
use Midnite81\Guardian\Store\RedisStore;

uses()->group('stores');

beforeEach(function () {
    $this->redis = Mockery::mock(Redis::class);
    $this->store = new RedisStore($this->redis);
});

afterEach(function () {
    Mockery::close();
});

it('can store and retrieve an item', function () {
    $key = 'test_key';
    $value = 'test_value';
    $jsonValue = json_encode($value);

    $this->redis->shouldReceive('set')
        ->with('guardian:' . $key, $jsonValue)
        ->andReturn(true);

    $this->redis->shouldReceive('get')
        ->with('guardian:' . $key)
        ->andReturn($jsonValue);

    expect($this->store->put($key, $value))->toBeTrue()
        ->and($this->store->get($key))->toBe($value);
});

it('returns null for non-existent key', function () {
    $this->redis->shouldReceive('get')
        ->with('guardian:non_existent_key')
        ->andReturn(false);

    expect($this->store->get('non_existent_key'))->toBeNull();
});

it('can check if an item exists', function () {
    $this->redis->shouldReceive('exists')
        ->with('guardian:existing_key')
        ->andReturn(1);

    $this->redis->shouldReceive('exists')
        ->with('guardian:non_existent_key')
        ->andReturn(0);

    expect($this->store->has('existing_key'))->toBeTrue()
        ->and($this->store->has('non_existent_key'))->toBeFalse();
});

it('can remove an item', function () {
    $this->redis->shouldReceive('del')
        ->with('guardian:remove_key')
        ->andReturn(1);

    expect($this->store->forget('remove_key'))->toBeTrue();
});

it('handles expiration correctly', function () {
    $key = 'expiring_key';
    $value = 'expiring_value';
    $jsonValue = json_encode($value);

    $this->redis->shouldReceive('setex')
        ->with('guardian:' . $key, 60, $jsonValue)
        ->andReturn(true);

    expect($this->store->put($key, $value, 60))->toBeTrue();
});

it('can store and retrieve complex data types', function () {
    $key = 'complex_key';
    $value = [
        'name' => 'John Doe',
        'age' => 30,
        'hobbies' => ['reading', 'swimming'],
    ];
    $jsonValue = json_encode($value);

    $this->redis->shouldReceive('set')
        ->with('guardian:' . $key, $jsonValue)
        ->andReturn(true);

    $this->redis->shouldReceive('get')
        ->with('guardian:' . $key)
        ->andReturn($jsonValue);

    expect($this->store->put($key, $value))->toBeTrue()
        ->and($this->store->get($key))->toBe($value);
});

it('handles different TTL types correctly', function () {
    $key = 'ttl_key';
    $value = 'ttl_value';
    $jsonValue = json_encode($value);

    // Test with int
    $this->redis->shouldReceive('setex')
        ->with('guardian:' . $key, 60, $jsonValue)
        ->andReturn(true);
    expect($this->store->put($key, $value, 60))->toBeTrue();

    // Test with DateInterval
    $this->redis->shouldReceive('setex')
        ->with('guardian:' . $key, 3600, $jsonValue)
        ->andReturn(true);
    expect($this->store->put($key, $value, new DateInterval('PT1H')))->toBeTrue();

    // Test with DateTime
    $future = new DateTime('+1 hour');
    $ttl = $future->getTimestamp() - time();
    $this->redis->shouldReceive('setex')
        ->with('guardian:' . $key, $ttl, $jsonValue)
        ->andReturn(true);
    expect($this->store->put($key, $value, $future))->toBeTrue();
});

it('uses the correct prefix for keys', function () {
    $customPrefix = 'custom:';
    $store = new RedisStore($this->redis, $customPrefix);
    $key = 'prefixed_key';
    $value = 'prefixed_value';
    $jsonValue = json_encode($value);

    $this->redis->shouldReceive('set')
        ->with($customPrefix . $key, $jsonValue)
        ->andReturn(true);

    $this->redis->shouldReceive('get')
        ->with($customPrefix . $key)
        ->andReturn($jsonValue);

    expect($store->put($key, $value))->toBeTrue()
        ->and($store->get($key))->toBe($value);
});

it('throws RedisStoreException on Redis exceptions', function () {
    $this->redis->shouldReceive('get')
        ->andThrow(new RedisException('Connection failed'));

    $this->store->get('some_key');
})->throws(RedisStoreException::class);

it('throws RedisStoreException on JSON encoding exceptions', function () {
    $key = 'invalid_json_key';
    $value = fopen('php://memory', 'r'); // This will cause a JSON encoding exception

    $this->store->put($key, $value);
})->throws(RedisStoreException::class);

it('throws RedisStoreException on Redis exceptions in put method', function () {
    $this->redis->shouldReceive('set')
        ->andThrow(new RedisException('Connection failed'));

    $this->store->put('some_key', 'some_value');
})->throws(RedisStoreException::class);

it('throws RedisStoreException on Redis exceptions in has method', function () {
    $this->redis->shouldReceive('exists')
        ->andThrow(new RedisException('Connection failed'));

    $this->store->has('some_key');
})->throws(RedisStoreException::class);

it('throws RedisStoreException on Redis exceptions in forget method', function () {
    $this->redis->shouldReceive('del')
        ->andThrow(new RedisException('Connection failed'));

    $this->store->forget('some_key');
})->throws(RedisStoreException::class);

it('throws RedisStoreException on JSON decoding exceptions in get method', function () {
    $this->redis->shouldReceive('get')
        ->andReturn('{"invalid: json}');

    $this->store->get('some_key');
})->throws(RedisStoreException::class);

it('throws RedisStoreException on Redis exceptions with expiration', function () {
    $key = 'expiring_key';
    $value = 'expiring_value';
    $jsonValue = json_encode($value);

    $this->redis->shouldReceive('setex')
        ->with('guardian:' . $key, 60, $jsonValue)
        ->andThrow(new RedisException('Connection failed'));

    $this->store->put($key, $value, 60);
})->throws(RedisStoreException::class);

it('returns default value when Redis returns a non-string value', function () {
    $key = 'non_string_key';
    $default = 'default_value';

    // Mock Redis to return a non-string value (e.g., an integer)
    $this->redis->shouldReceive('get')
        ->with('guardian:' . $key)
        ->andReturn(123); // Return an integer instead of a string

    $result = $this->store->get($key, $default);

    expect($result)->toBe($default);
});
