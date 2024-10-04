<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Store;

use DateInterval;
use DateTimeInterface;
use JsonException;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Exceptions\Store\FileStoreException;
use Midnite81\Guardian\Helpers\System;

/**
 * FileStore class for caching data in files.
 *
 * This class provides methods to store and retrieve data using the file system.
 */
class FileStore implements CacheInterface
{
    /**
     * @var System
     */
    protected System $system;

    /**
     * FileStore constructor.
     *
     * @param string $basePath The base path for cache files.
     * @param System|null $system The system helper instance.
     *
     * @throws FileStoreException If the cache directory cannot be created.
     */
    public function __construct(
        protected string $basePath,
        ?System $system = null
    ) {
        $this->system = $system ?? new System;
        $this->ensureDirectoryExists();
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileStoreException If the cache file cannot be written.
     */
    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): bool
    {
        $expiration = $this->getExpirationTime($ttl);

        $data = [
            'value' => $value,
            'expiration' => $expiration,
        ];

        $filename = $this->getFilename($key);

        try {
            $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new FileStoreException($e->getMessage(), $e->getCode(), $e);
        }

        $result = $this->system->filePutContents($filename, $jsonData) !== false;
        if (!$result) {
            throw new FileStoreException("Failed to write cache file: $filename");
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileStoreException If the cache file cannot be read or contains invalid data.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $filename = $this->getFilename($key);

        if (!$this->system->fileExists($filename)) {
            return $default;
        }

        $content = $this->system->fileGetContents($filename);
        if ($content === false) {
            throw new FileStoreException("Failed to read cache file: $filename");
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new FileStoreException($e->getMessage(), $e->getCode(), $e);
        }

        /* @phpstan-ignore-next-line */
        if (!array_key_exists('expiration', $data) || !array_key_exists('value', $data)) {
            throw new FileStoreException('Invalid cache data format');
        }

        if ($data['expiration'] !== null && $data['expiration'] <= time()) {
            $this->system->unlink($filename);

            return $default;
        }

        return $data['value'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileStoreException If the cache file contains invalid data.
     */
    public function has(string $key): bool
    {
        $filename = $this->getFilename($key);

        if (!$this->system->fileExists($filename)) {
            return false;
        }

        $content = $this->system->fileGetContents($filename);
        if ($content === false) {
            return false;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new FileStoreException($e->getMessage(), $e->getCode(), $e);
        }

        /* @phpstan-ignore-next-line */
        if (!array_key_exists('expiration', $data) || !array_key_exists('value', $data)) {
            return false;
        }

        if ($data['expiration'] !== null && $data['expiration'] <= time()) {
            $this->system->unlink($filename);

            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string $key): bool
    {
        $filename = $this->getFilename($key);

        if ($this->system->fileExists($filename)) {
            return $this->system->unlink($filename);
        }

        return false;
    }

    /**
     * Get the filename for a given cache key.
     *
     * @param string $key The cache key.
     * @return string The full path to the cache file.
     */
    public function getFilename(string $key): string
    {
        return $this->basePath . '/guardian_cache/' . hash('sha256', $key) . '.cache';
    }

    /**
     * Ensure the cache directory exists.
     *
     * @throws FileStoreException If the directory cannot be created.
     */
    protected function ensureDirectoryExists(): void
    {
        $path = $this->basePath . '/guardian_cache';
        if (!$this->system->isDir($path)) {
            if (!$this->system->mkdir($path, 0755, true)) {
                throw new FileStoreException("Directory \"$path\" was not created");
            }
        }
    }

    /**
     * Calculate the expiration time based on the given TTL.
     *
     * @param DateInterval|DateTimeInterface|int|null $ttl The Time To Live.
     * @return int|null The expiration timestamp or null for no expiration.
     */
    protected function getExpirationTime(DateInterval|DateTimeInterface|int|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $ttl = (new \DateTime)->add($ttl)->getTimestamp() - time();
        } elseif ($ttl instanceof DateTimeInterface) {
            $ttl = $ttl->getTimestamp() - time();
        }

        return time() + max(0, $ttl);
    }
}
