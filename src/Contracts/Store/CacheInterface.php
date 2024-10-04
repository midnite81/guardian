<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Contracts\Store;

use DateInterval;
use DateTimeInterface;

interface CacheInterface
{
    /**
     * Retrieves the value associated with the specified key.
     *
     * @param string $key The key for which to retrieve the value.
     * @param mixed $default The default value to return if the key doesn't exist.
     * @return mixed The value associated with the specified key or the default value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Checks if the specified key exists in the cache.
     *
     * @param string $key The key to check for existence.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Stores the specified key-value pair with an optional time-to-live value.
     *
     * @param string $key The key under which the value should be stored.
     * @param mixed $value The value to be stored.
     * @param DateInterval|DateTimeInterface|int|null $ttl The time-to-live for the stored item.
     * @return bool True if the value was successfully stored, false otherwise.
     */
    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): bool;

    /**
     * Removes an item from the cache.
     *
     * @param string $key The key of the item to remove.
     * @return bool True if the item was successfully removed, false otherwise.
     */
    public function forget(string $key): bool;
}
