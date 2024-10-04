<?php

declare(strict_types=1);

use Midnite81\Guardian\Exceptions\Store\FileStoreException;
use Midnite81\Guardian\Helpers\System;
use Midnite81\Guardian\Store\FileStore;
use PHPUnit\Framework\Assert;

uses()->group('stores');

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/guardian_test_' . uniqid();
    mkdir($this->tempDir, 0755, true);
    try {
        $this->store = new FileStore($this->tempDir);
    } catch (FileStoreException $e) {
        Assert::fail('Cannot construct the store. ' . $e->getMessage());
    }
    $this->mockSystem = Mockery::mock(System::class);
});

afterEach(function () {
    recursiveRemove($this->tempDir);
    Mockery::close();
});

function recursiveRemove($dir): bool
{
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? recursiveRemove($path) : unlink($path);
    }

    return rmdir($dir);
}

it('can store and retrieve an item', function () {
    $key = 'test_key';
    $value = 'test_value';

    $putResult = $this->store->put($key, $value);
    expect($putResult)->toBeTrue();

    $filename = $this->store->getFilename($key);

    $fileContents = file_get_contents($filename);

    $decodedContents = json_decode($fileContents, true);

    $retrievedValue = $this->store->get($key);

    expect($retrievedValue)->toBe($value);
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

    sleep(2); // Wait for expiration
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
});

it('returns default value for non-existent or expired keys', function () {
    $default = 'default_value';
    expect($this->store->get('non_existent_key', $default))->toBe($default);

    $key = 'expiring_key';
    $value = 'expiring_value';
    $this->store->put($key, $value, 1);
    sleep(2);
    expect($this->store->get($key, $default))->toBe($default);
});

it('creates storage directory if it does not exist', function () {
    $newTempDir = sys_get_temp_dir() . '/guardian_test_' . uniqid();
    expect(is_dir($newTempDir))->toBeFalse();

    new FileStore($newTempDir);
    expect(is_dir($newTempDir . '/guardian_cache'))->toBeTrue();

    recursiveRemove($newTempDir);
});

it('uses correct file naming convention', function () {
    $key = 'test_key';
    $value = 'test_value';

    $this->store->put($key, $value);
    $expectedFilename = $this->tempDir . '/guardian_cache/' . hash('sha256', $key) . '.cache';
    expect(file_exists($expectedFilename))->toBeTrue();
});

it('throws exception when cache file contains invalid JSON', function () {
    $key = 'invalid_json_key';
    $filename = $this->tempDir . '/guardian_cache/' . hash('sha256', $key) . '.cache';

    // Write invalid JSON to the cache file
    file_put_contents($filename, 'invalid json');

    expect(fn () => $this->store->get($key))
        ->toThrow(FileStoreException::class, 'Syntax error');
});

it('throws FileStoreException in has() method when cache file contains invalid JSON', function () {
    $key = 'invalid_json_key';
    $filename = $this->tempDir . '/guardian_cache/' . hash('sha256', $key) . '.cache';

    // Write invalid JSON to the cache file
    file_put_contents($filename, 'invalid json');

    expect(fn () => $this->store->has($key))->toThrow(FileStoreException::class);
});

it('throws FileStoreException when trying to store non-JSON-encodable data', function () {
    $key = 'non_encodable_key';
    $value = fopen('php://memory', 'r'); // Resources are not JSON-encodable

    expect(fn () => $this->store->put($key, $value))->toThrow(FileStoreException::class);

    fclose($value);
});

it('handles missing keys in cache data', function () {
    $key = 'missing_keys';
    $filename = $this->tempDir . '/guardian_cache/' . hash('sha256', $key) . '.cache';

    // Write JSON with missing keys
    file_put_contents($filename, json_encode(['some_other_key' => 'value']));

    expect(fn () => $this->store->get($key))->toThrow(FileStoreException::class);
});

it('handles expired cache items', function () {
    $key = 'expired_key';
    $value = 'expired_value';

    // Set expiration to 1 second ago
    $expiredTime = time() - 1;
    $data = [
        'value' => $value,
        'expiration' => $expiredTime,
    ];

    $filename = $this->tempDir . '/guardian_cache/' . hash('sha256', $key) . '.cache';
    file_put_contents($filename, json_encode($data));

    expect($this->store->get($key))->toBeNull()
        ->and(file_exists($filename))->toBeFalse();
});

it('returns false when the file doesnt exist when forgetting', function () {
    expect($this->store->forget('non_existent_key'))->toBeFalse();
});

it('throws FileStoreException when file_put_contents fails', function () {
    $key = 'unwritable_key';
    $value = 'unwritable_value';

    // Create a mock of the System class
    $mockSystem = Mockery::mock(System::class);

    // Set up expectations for the mock
    $mockSystem->shouldReceive('isDir')->andReturn(true); // Assume directory exists
    $mockSystem->shouldReceive('filePutContents')->once()->andReturn(false); // Simulate write failure

    // Create a new FileStore instance with the mocked System
    $store = new FileStore($this->tempDir, $mockSystem);

    // Test that the exception is thrown
    expect(fn () => $store->put($key, $value))
        ->toThrow(FileStoreException::class, 'Failed to write cache file');
});

it('throws an error when it cannot create the directory', function () {
    $tempDir = sys_get_temp_dir() . '/guardian_test_' . uniqid();

    $mockSystem = Mockery::mock(System::class);
    $mockSystem->shouldReceive('isDir')->andReturn(false);
    $mockSystem->shouldReceive('mkdir')->andReturn(false);

    expect(fn () => new FileStore($tempDir, $mockSystem))
        ->toThrow(FileStoreException::class);
});

it('throws FileStoreException when unable to read cache file', function () {
    $key = 'unreadable_key';
    $basePath = '/path/to/base';

    $mockSystem = Mockery::mock(System::class);
    $mockSystem->shouldReceive('isDir')->once()->with($basePath . '/guardian_cache')->andReturn(true);
    $mockSystem->shouldReceive('fileExists')->once()->andReturn(true);
    $mockSystem->shouldReceive('fileGetContents')->once()->andReturn(false);

    $store = new FileStore($basePath, $mockSystem);

    expect(fn () => $store->get($key))
        ->toThrow(FileStoreException::class, 'Failed to read cache file:');
});

it('returns true for existing non-expired item using has method', function () {
    $key = 'existing_key';
    $value = 'existing_value';

    $this->store->put($key, $value);

    expect($this->store->has($key))->toBeTrue();
});

it('returns false for expired item using has method', function () {
    $key = 'expired_key';
    $value = 'expired_value';

    $this->store->put($key, $value, 1); // Expire in 1 second
    sleep(2);

    expect($this->store->has($key))->toBeFalse();
});

it('throws FileStoreException when cache file exists but cannot be read', function () {
    $key = 'unreadable_key';
    $basePath = sys_get_temp_dir() . '/guardian_test_' . uniqid();

    $mockSystem = Mockery::mock(\Midnite81\Guardian\Helpers\System::class);
    $mockSystem->shouldReceive('isDir')->andReturn(true);
    $mockSystem->shouldReceive('fileExists')->andReturn(true);
    $mockSystem->shouldReceive('fileGetContents')->andReturn(false);

    $store = new FileStore($basePath, $mockSystem);

    expect(fn () => $store->get($key))->toThrow(FileStoreException::class);
});

it('returns false when cache file exists but cannot be read in has method', function () {
    $key = 'unreadable_key';
    $basePath = sys_get_temp_dir() . '/guardian_test_' . uniqid();

    $mockSystem = Mockery::mock(\Midnite81\Guardian\Helpers\System::class);
    $mockSystem->shouldReceive('isDir')->andReturn(true);
    $mockSystem->shouldReceive('fileExists')->andReturn(true);
    $mockSystem->shouldReceive('fileGetContents')->andReturn(false);

    $store = new FileStore($basePath, $mockSystem);

    expect($store->has($key))->toBeFalse();
});

it('returns false when cache file doesn\'t have correct data points', function () {
    $key = 'unreadable_key';
    $basePath = sys_get_temp_dir() . '/guardian_test_' . uniqid();

    $mockSystem = Mockery::mock(\Midnite81\Guardian\Helpers\System::class);
    $mockSystem->shouldReceive('isDir')->andReturn(true);
    $mockSystem->shouldReceive('fileExists')->andReturn(true);
    $mockSystem->shouldReceive('fileGetContents')->andReturn(json_encode(['foo' => 'bar']));

    $store = new FileStore($basePath, $mockSystem);

    expect($store->has($key))->toBeFalse();
});
