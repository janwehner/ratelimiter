<?php

namespace ArtisanSdk\RateLimiter\Tests\Stubs;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Psr\SimpleCache\InvalidArgumentException;

class Cache implements Repository
{
    /**
     * The storage implementation.
     */
    protected array $storage = [];

    /**
     * Determine if an item exists in the cache.
     */
    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * Retrieve an item from the cache by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage[$key] ?? $default;
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     */
    public function pull($key, $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->forget($key);

        return $value;
    }

    /**
     * Store an item in the cache.
     *
     * @param string                                     $key
     * @param \DateTimeInterface|\DateInterval|float|int $ttl
     */
    public function put($key, $value, $ttl = null)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string                                     $key
     * @param \DateTimeInterface|\DateInterval|float|int $ttl
     */
    public function add($key, $value, $ttl = null): bool
    {
        if ( ! $this->has($key)) {
            $this->put($key, $value, $ttl);

            return true;
        }

        return false;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     */
    public function increment($key, $value = 1): bool|int
    {
        $this->storage[$key] = ! isset($this->storage[$key])
                ? $value : ((int) $this->storage[$key]) + $value;

        return $this->storage[$key];
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     */
    public function decrement($key, $value = 1): bool|int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string                                     $key
     * @param \DateTimeInterface|\DateInterval|float|int $ttl
     */
    public function remember($key, $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if ( ! is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string $key
     */
    public function sear($key, \Closure $callback): mixed
    {
        return $this->rememberForever($key, $callback);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string $key
     */
    public function rememberForever($key, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if ( ! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function forget($key): bool
    {
        return $this->delete($key);
    }

    /**
     * Get the cache store implementation.
     */
    public function getStore(): Store|array
    {
        return $this->storage; // this is return type non-compliant
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   the key of the item to store
     * @param mixed                  $value the value of the item to store, must be serializable
     * @param int|\DateInterval|null $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool true on success and false on failure*
     */
    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $this->storage[$key] = $value;

        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key the unique cache key of the item to delete
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function delete(string $key): bool
    {
        unset($this->storage[$key]);

        return true;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool true on success and false on failure
     */
    public function clear(): bool
    {
        $this->storage = [];

        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    a list of keys that can obtained in a single operation
     * @param mixed    $default default value to return for keys that do not exist
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];

        foreach ($keys as $key => $value) {
            if ($this->has($key)) {
                $values[$key] = $this->get($key);
            }
        }

        return $values;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values a list of key => value pairs for a multiple-set operation
     * @param int|\DateInterval|null $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool true on success and false on failure
     */
    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if ( ! $this->set($key, $value, $ttl)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys a list of string-based keys to be deleted
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            if ( ! $this->delete($key)) {
                return false;
            }
        }

        return true;
    }
}
