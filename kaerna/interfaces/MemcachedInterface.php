<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 21:54
 */

namespace DHP\kaerna\interfaces;


interface MemcachedInterface
{
    /**
     * Connects to memcached server.
     *
     * MemcachedInterface constructor.
     * @param string $instanceName
     * @param array $servers
     * @param array $options
     * @param string $prefix prefix for this memcache storage, usually ''
     */
    public function __construct(
        string $instanceName = 'DHP_Karna',
        array $servers = [['127.0.0.1', 11211]],
        array $options = [
            \Memcached::OPT_CONNECT_TIMEOUT => 10,
            \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
            \Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
            \Memcached::OPT_REMOVE_FAILED_SERVERS => \true,
            \Memcached::SERIALIZER_JSON => \true,
            \Memcached::OPT_RETRY_TIMEOUT => 1,
            \Memcached::OPT_BINARY_PROTOCOL => \false
        ],
        int $defaultExpires = 604800,
        string $prefix = ''
    );

    /**
     * Increment a key.
     *
     * @param string $key
     * @param int $incrementBy if supplied, increment by this, usually one
     * @param int $initialValue
     * @param int $expires Number of seconds to store this or timestamp when it expires
     * @return bool
     */
    public function increment(string $key, int $incrementBy = 1, int $initialValue = 0, int $expires = null): bool;

    /**
     * Decrement key
     *
     * @param string $key
     * @param int $decrementBy if supplied, decrements by this, usually one
     * @param int $initialValue
     * @param int $expires Number of seconds to store this or timestamp when it expires
     * @return bool
     */
    public function decrement(string $key, int $decrementBy = 1, int $initialValue = 0, int $expires = null): bool;

    /**
     * Store $key permanently.
     *
     * @param string $key
     * @param $value
     * @return bool
     */
    public function permanent(string $key, $value): bool;

    public function flush(): bool;

    /**
     * Sets key to value.
     * @param string $key
     * @param $value The value to set
     * @param int $expires Number of seconds to store this or timestamp when it expires
     * @return bool
     */
    public function set(string $key, $value, int $expires = null): bool;

    /**
     * Get value for key or return default value
     * @param string $key
     * @param null | mixed $defaultValue returned when key is not found, null usually
     * @return null | mixed
     */
    public function get(string $key, $defaultValue = null);

    /**
     * Clears key
     * @param string $key
     * @return bool
     */
    public function clear(string $key): bool;

    /**
     * Checks if we are connected to a memcache server.
     * @return bool
     */
    public function isConnected(): bool;
}