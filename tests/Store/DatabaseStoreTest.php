<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Midnite81\Guardian\Exceptions\Store\DatabaseStoreException;
use Midnite81\Guardian\Store\DatabaseStore;

uses()->group('stores');

beforeEach(function () {
    $this->pdo = new PDO('sqlite::memory:');
    $this->store = new DatabaseStore($this->pdo);
});

afterEach(function () {
    Mockery::close();
});

it('can store and retrieve an item', function () {
    $key = 'test_key';
    $value = 'test_value';

    expect($this->store->put($key, $value))->toBeTrue()
        ->and($this->store->get($key))->toBe($value);
});

it('returns null for non-existent key', function () {
    expect($this->store->get('non_existent_key'))->toBeNull();
});

it('can check if an item exists', function () {
    $key = 'existing_key';
    $value = 'existing_value';

    $this->store->put($key, $value);

    expect($this->store->has($key))->toBeTrue()
        ->and($this->store->has('non_existent_key'))->toBeFalse();
});

it('can remove an item', function () {
    $key = 'remove_key';
    $value = 'remove_value';

    $this->store->put($key, $value);
    expect($this->store->forget($key))->toBeTrue()
        ->and($this->store->get($key))->toBeNull();
});

it('handles expiration correctly', function () {
    $key = 'expiring_key';
    $value = 'expiring_value';

    $this->store->put($key, $value, 1); // Expire in 1 second
    expect($this->store->get($key))->toBe($value);

    // Simulate time passing
    $stmt = $this->pdo->prepare('UPDATE guardian_cache SET expiration = :expiration WHERE `key` = :key');
    $stmt->execute(['expiration' => time() - 1, 'key' => $key]);

    expect($this->store->get($key))->toBeNull();
});

it('can store and retrieve complex data types', function () {
    $key = 'complex_key';
    $value = [
        'name' => 'John Doe',
        'age' => 30,
        'hobbies' => ['reading', 'swimming'],
    ];

    $this->store->put($key, $value);
    expect($this->store->get($key))->toBe($value);
});

it('handles different TTL types correctly', function () {
    $key = 'ttl_key';
    $value = 'ttl_value';

    // Test with int
    $this->store->put($key, $value, 60);
    expect($this->store->get($key))->toBe($value);

    // Test with DateInterval
    $this->store->put($key, $value, new DateInterval('PT1H'));
    expect($this->store->get($key))->toBe($value);

    // Test with DateTime
    $future = new DateTime('+1 hour');
    $this->store->put($key, $value, $future);
    expect($this->store->get($key))->toBe($value);

    // Verify expiration times
    $stmt = $this->pdo->prepare('SELECT expiration FROM guardian_cache WHERE `key` = :key');
    $stmt->execute(['key' => $key]);
    $expiration = $stmt->fetchColumn();
    expect($expiration)->toBeGreaterThan(time());
});

it('returns default value for non-existent or expired keys', function () {
    $default = 'default_value';
    expect($this->store->get('non_existent_key', $default))->toBe($default);

    $key = 'expiring_key';
    $value = 'expiring_value';
    $this->store->put($key, $value, 1);

    // Simulate time passing
    $stmt = $this->pdo->prepare('UPDATE guardian_cache SET expiration = :expiration WHERE `key` = :key');
    $stmt->execute(['expiration' => time() - 1, 'key' => $key]);

    expect($this->store->get($key, $default))->toBe($default);
});

it('creates the cache table if it does not exist', function () {
    // Drop the table if it exists
    $this->pdo->exec('DROP TABLE IF EXISTS guardian_cache');

    // Create a new store, which should recreate the table
    $newStore = new DatabaseStore($this->pdo);

    // Try to insert a value
    $key = 'test_key';
    $value = 'test_value';
    expect($newStore->put($key, $value))->toBeTrue();

    // Verify the value was inserted
    expect($newStore->get($key))->toBe($value);
});

it('throws DatabaseStoreException on PDO exceptions in get method', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('prepare')->andThrow(new PDOException('Database error'));
    $pdoMock->shouldReceive('exec')->andReturn(true); // Mock exec method
    $store = new DatabaseStore($pdoMock);

    $store->get('some_key');
})->throws(DatabaseStoreException::class);

it('throws DatabaseStoreException on PDO exceptions in put method', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('prepare')->andThrow(new PDOException('Database error'));
    $pdoMock->shouldReceive('exec')->andReturn(true); // Mock exec method
    $store = new DatabaseStore($pdoMock);

    $store->put('some_key', 'some_value');
})->throws(DatabaseStoreException::class);

it('throws DatabaseStoreException on PDO exceptions in has method', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('prepare')->andThrow(new PDOException('Database error'));
    $pdoMock->shouldReceive('exec')->andReturn(true); // Mock exec method
    $store = new DatabaseStore($pdoMock);

    $store->has('some_key');
})->throws(DatabaseStoreException::class);

it('throws exception in forget method', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('prepare')->andThrow(new PDOException('Database error'));
    $pdoMock->shouldReceive('exec')->andReturn(true); // Mock exec method
    $store = new DatabaseStore($pdoMock);

    expect(fn () => $store->forget('some_key'))->toThrow(DatabaseStoreException::class);
});

it('throws DatabaseStoreException on JSON encoding exceptions in put method', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('exec')->andReturn(true); // Mock exec method
    $store = new DatabaseStore($pdoMock);

    $key = 'invalid_json_key';
    $value = fopen('php://memory', 'r'); // This will cause a JSON encoding exception

    $store->put($key, $value);
})->throws(DatabaseStoreException::class);

it('handles JSON decoding exceptions gracefully in get method', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('exec')->andReturn(true); // Mock exec method
    $pdoMock->shouldReceive('prepare')->andReturn(
        Mockery::mock(PDOStatement::class)
            ->shouldReceive('execute')->andReturn(true)
            ->shouldReceive('fetch')->andReturn([
                'value' => '{"invalid: json}',
                'expiration' => null,
            ])
            ->getMock()
    );
    $store = new DatabaseStore($pdoMock);

    expect($store->get('invalid_json_key', 'default'))->toBe('default');
});

it('throws PDO exceptions when creating table', function () {
    $pdoMock = Mockery::mock(PDO::class);
    $pdoMock->shouldReceive('exec')->andThrow(new PDOException('Database error'));

    // We're testing that no exception is thrown when creating the table
    expect(fn () => new DatabaseStore($pdoMock))->toThrow(DatabaseStoreException::class);
});

it('returns false for expired items in has method', function () {
    $key = 'expired_key';
    $value = 'expired_value';

    // Store an item with a short expiration time
    $this->store->put($key, $value, 1); // Expire in 1 second

    // Simulate time passing
    $stmt = $this->pdo->prepare('UPDATE guardian_cache SET expiration = :expiration WHERE `key` = :key');
    $stmt->execute(['expiration' => time() - 1, 'key' => $key]);

    // Check if the key exists (it shouldn't, because it's expired)
    expect($this->store->has($key))->toBeFalse();
});

it('returns default value when stored value is not a string', function () {
    // Mock PDO and PDOStatement
    $pdoMock = Mockery::mock(PDO::class);
    $pdoStatementMock = Mockery::mock(PDOStatement::class);

    // Setup expectations
    $pdoMock->shouldReceive('exec')->once()->andReturn(true); // For createTable in constructor
    $pdoMock->shouldReceive('prepare')->once()->andReturn($pdoStatementMock);
    $pdoStatementMock->shouldReceive('execute')->once()->with(['key' => 'non_string_key'])->andReturn(true);
    $pdoStatementMock->shouldReceive('fetch')->once()->with(PDO::FETCH_ASSOC)->andReturn([
        'key' => 'non_string_key',
        'value' => ['This is an array, not a string'],
        'expiration' => null,
    ]);

    // Create DatabaseStore instance with mocked PDO
    $store = new DatabaseStore($pdoMock);

    // Test get method with a key that has a non-string value
    $defaultValue = 'default_value';
    $result = $store->get('non_string_key', $defaultValue);

    // Assert that the default value is returned
    expect($result)->toBe($defaultValue);
});
