<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Midnite81\Guardian\Store;

use DateInterval;
use DateTime;
use DateTimeInterface;
use JsonException;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Exceptions\Store\RedisStoreException;
use Redis;
use RedisException;

/**
 * Redis implementation of the CacheInterface.
 */
class RedisStore implements CacheInterface
{
    /**
     * Constructor method for initializing the class with Redis connection and a prefix.
     *
     * @param Redis $redis The Redis connection instance.
     * @param string $prefix The prefix to be used for Redis keys.
     */
    public function __construct(
        protected Redis $redis,
        protected string $prefix = 'guardian:'
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws RedisStoreException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $value = $this->redis->get($this->prefix . $key);
            if ($value === false) {
                return $default;
            }

            if (!is_string($value)) {
                return $default;
            }

            $result = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return $result ?? $default;
        } catch (RedisException|JsonException $e) {
            throw new RedisStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws RedisStoreException
     */
    public function has(string $key): bool
    {
        try {
            return (bool) $this->redis->exists($this->prefix . $key);
        } catch (RedisException $e) {
            throw new RedisStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws RedisStoreException
     */
    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): bool
    {
        try {
            $jsonValue = json_encode($value, JSON_THROW_ON_ERROR);
            $ttlInSeconds = $this->getTtlInSeconds($ttl);

            if ($ttlInSeconds === null) {
                return (bool) $this->redis->set($this->prefix . $key, $jsonValue);
            }

            return (bool) $this->redis->setex($this->prefix . $key, $ttlInSeconds, $jsonValue);
        } catch (RedisException|JsonException $e) {
            throw new RedisStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws RedisStoreException
     */
    public function forget(string $key): bool
    {
        try {
            return (bool) $this->redis->del($this->prefix . $key);
        } catch (RedisException $e) {
            throw new RedisStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Convert the given time-to-live (TTL) value to a number of seconds.
     *
     * @param DateInterval|DateTimeInterface|int|null $ttl The TTL value to convert.
     * @return int|null The TTL value in seconds or null if the given TTL is null.
     */
    protected function getTtlInSeconds(DateInterval|DateTimeInterface|int|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return (new DateTime)->add($ttl)->getTimestamp() - time();
        }

        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp() - time();
        }

        return $ttl;
    }
}
