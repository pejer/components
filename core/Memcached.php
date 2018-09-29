<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 22:05
 */

namespace DHP_Karna\core;

use DHP_Karna\core\kernel\MemcachedInterface;

class Memcached implements MemcachedInterface
{
    /** @var \Memcached */
    private $server;
    /**
     * @var string
     */
    private $prefix;
    /** @var int  */
    private $defaultExpires;
    /** @var bool  */
    private $connected;

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
    ) {
        $this->server = new \Memcached($instanceName);
        $this->server->setOptions($options);
        if (\count($this->server->getServerList()) != \count($servers)) {
            $this->server->resetServerList();
            $this->server->addServers($servers);
        }
        $this->connected = $this->server->getStats() === false ? false : true;
        $this->now       = \time();
        $this->setPrefix($prefix);
        $this->defaultExpires = $defaultExpires;
    }

    /**
     * Increment a key.
     *
     * @param string $key
     * @param int $incrementBy if supplied, increment by this, usually one
     * @param int $initialValue initial value of counter, default zero
     * @param int $expires Number of seconds to store this or timestamp when it expires
     * @return bool
     */
    public function increment(string $key, int $incrementBy = 1, int $initialValue = 0, int $expires = null): bool
    {
        $expires = $this->makeExpireTimestamp($expires);
        if (!$this->has($key)) {
            $this->set($key, $initialValue, $expires);
        }
        return $this->server->increment($key, $incrementBy);
    }

    /**
     * Decrement key
     *
     * This will not decrement past zero so the lowest value is zero
     *
     * @param string $key
     * @param int $decrementBy if supplied, decrements by this, usually one
     * @param int $initialValue initial value of counter, default zero
     * @param int $expires Number of seconds to store this or timestamp when it expires
     * @return bool
     */
    public function decrement(string $key, int $decrementBy = 1, int $initialValue = 0, int $expires = null): bool
    {
        $expires = $this->makeExpireTimestamp($expires);
        $expires = $this->makeExpireTimestamp($expires);
        if (!$this->has($key)) {
            $this->set($key, $initialValue, $expires);
        }
        return $this->server->decrement($key, $decrementBy);
    }

    /**
     * Store $key permanently.
     *
     * @param string $key
     * @param $value
     * @return bool
     */
    public function permanent(string $key, $value): bool
    {
        return $this->set($key, $value, 0);
    }

    /**
     * Sets key to value.
     * @param string $key
     * @param $value The value to set
     * @param int $expires Number of seconds to store this or timestamp when it expires
     * @return bool
     */
    public function set(string $key, $value, int $expires = null): bool
    {
        $key     = $this->makeKey($key);
        $expires = $this->makeExpireTimestamp($expires);
        return $this->server->set($key, $value, $expires);
    }

    /**
     * Get value for key or return default value
     * @param string $key
     * @param null | mixed $defaultValue returned when key is not found, null usually
     * @return null | mixed
     */
    public function get(string $key, $defaultValue = null)
    {
        $key    = $this->makeKey($key);
        $return = $this->server->get($key);
        return $this->server->getResultCode() === \Memcached::RES_NOTFOUND ? $defaultValue : $return;
    }

    public function has(string $key)
    {
        $key    = $this->makeKey($key);
        $return = $this->server->get($key);
        return $this->server->getResultCode() === \Memcached::RES_NOTFOUND ? false : true;
    }

    /**
     * Clears key
     * @param string $key
     * @return bool
     */
    public function clear(string $key): bool
    {
        // TODO: Implement clear() method.
    }

    private function makeKey(string $key)
    {
        return $this->prefix . $key;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = empty($prefix) ? '' : $prefix . ':';
    }

    private function makeExpireTimestamp(int $timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = $this->defaultExpires;
        }
        return ($timestamp > $this->now || $timestamp === 0) ? $timestamp : $timestamp + \time();
    }

    /**
     * Checks if we are connected to a memcache server.
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function flush(): bool
    {
        return $this->server->flush();
    }
}
