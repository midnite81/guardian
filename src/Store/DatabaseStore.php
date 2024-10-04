<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Midnite81\Guardian\Store;

use DateInterval;
use DateMalformedStringException;
use DateTime;
use DateTimeInterface;
use JsonException;
use Midnite81\Guardian\Contracts\Store\CacheInterface;
use Midnite81\Guardian\Exceptions\Store\DatabaseStoreException;
use PDO;
use PDOException;

/**
 * DatabaseStore implements a database-based storage mechanism for the Guardian cache.
 *
 * This class provides methods to store, retrieve, check the existence of,
 * and remove cache items using a database. It implements the CacheInterface to ensure
 * compatibility with the Guardian system.
 */
class DatabaseStore implements CacheInterface
{
    /**
     * DatabaseStore constructor.
     *
     * @param PDO $pdo The PDO instance for database operations.
     * @param string $tableName The name of the cache table.
     *
     * @throws DatabaseStoreException If table creation fails.
     */
    public function __construct(
        protected PDO $pdo,
        protected string $tableName = 'guardian_cache'
    ) {
        $this->createTable();
    }

    /**
     * {@inheritDoc}
     *
     * @throws DatabaseStoreException If a database error occurs.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} WHERE `key` = :key");
            $stmt->execute(['key' => $key]);
            /** @var array{expiration: int|null, value: mixed}|false $result */
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return $default;
            }

            if ($result['expiration'] !== null && $result['expiration'] <= time()) {
                $this->forget($key);

                return $default;
            }

            if (is_string($result['value'])) {
                $value = json_decode($result['value'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $default;
                }

                return $value;
            }

            return $default;
        } catch (PDOException $e) {
            throw new DatabaseStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DatabaseStoreException If a database error occurs.
     */
    public function has(string $key): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->tableName} WHERE `key` = :key AND (`expiration` IS NULL OR `expiration` > :time)");
            $stmt->execute(['key' => $key, 'time' => time()]);

            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            throw new DatabaseStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DateMalformedStringException If the date string is malformed.
     * @throws DatabaseStoreException If a database error occurs.
     */
    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): bool
    {
        try {
            $expiration = $this->getExpirationTime($ttl);
            $jsonValue = json_encode($value, JSON_THROW_ON_ERROR);

            $stmt = $this->pdo->prepare("REPLACE INTO {$this->tableName} (`key`, `value`, `expiration`) VALUES (:key, :value, :expiration)");

            return $stmt->execute([
                'key' => $key,
                'value' => $jsonValue,
                'expiration' => $expiration,
            ]);
        } catch (PDOException|JsonException $e) {
            throw new DatabaseStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DatabaseStoreException If a database error occurs.
     */
    public function forget(string $key): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE `key` = :key");
            $stmt->execute(['key' => $key]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new DatabaseStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates the cache table if it doesn't exist.
     *
     * @return void
     *
     * @throws DatabaseStoreException If table creation fails.
     */
    protected function createTable(): void
    {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS {$this->tableName} (
                `key` VARCHAR(255) PRIMARY KEY,
                `value` TEXT,
                `expiration` INT
            )");
        } catch (PDOException $e) {
            throw new DatabaseStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Calculates the expiration timestamp based on the provided TTL.
     *
     * @param DateInterval|DateTimeInterface|int|null $ttl The TTL value.
     * @return int|null The expiration timestamp or null for no expiration.
     *
     * @throws DateMalformedStringException If the date string is malformed.
     */
    protected function getExpirationTime(DateInterval|DateTimeInterface|int|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        $now = new DateTime;

        if ($ttl instanceof DateInterval) {
            return $now->add($ttl)->getTimestamp();
        }

        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        return $now->modify("+$ttl seconds")->getTimestamp();
    }
}
