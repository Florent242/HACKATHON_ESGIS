<?php

namespace Auth\Model;

use Redis;
use Exception;

/**
 * Summary of RedisManager
 */
class RedisManager
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect($_ENV['REDIS_HOST'] ?? '127.0.0.1', $_ENV['REDIS_PORT'] ?? 6379);
        $this->redis->auth($_ENV['REDIS_PASSWORD'] ?? 'Qwertyui1?');
    }

    public function set($key, $value, $ttl = 3600)
    {
        return $this->redis->setex($key, $ttl, $value);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function delete($key)
    {
        return $this->redis->del($key);
    }

    public function increment($key, $ttl = 60)
    {
        $exists = $this->redis->exists($key);
        $count = $this->redis->incr($key);
        if (!$exists) {
            $this->redis->expire($key, $ttl);
        }
        return $count;
    }

    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }

    /**
     * fonction expire, expire une clé avec un ttl, le ttl est en secondes
     * @param string $key
     * @param int $ttl
     * @return bool
     */
    public function expire($key, $ttl = 60)
    {
        return $this->redis->expire($key, $ttl);
    }
}
