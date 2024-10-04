<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Store;

use DateInterval;
use DateTimeInterface;
use Illuminate\Cache\Repository;
use Midnite81\Guardian\Contracts\Store\CacheInterface;

class LaravelStore implements CacheInterface
{
    /**
     * Constructor method for initializing the class with a Repository object.
     *
     * @param Repository $cache An instance of a cache repository.
     * @return void
     */
    public function __construct(protected Repository $cache)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * {@inheritDoc}
     */
    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): bool
    {
        return $this->cache->put($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }
}
