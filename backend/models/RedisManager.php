<?php
namespace Auth\Model;

use Redis;
use Exception;
/**
 * Summary of RedisManager
 */
class RedisManager {
    private $redis;

    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379); // Ou change en fonction de ta config
    }

    public function set($key, $value, $ttl = 3600) {
        return $this->redis->setex($key, $ttl, $value);
    }

    public function get($key) {
        return $this->redis->get($key);
    }

    public function delete($key) {
        return $this->redis->del($key);
    }

    public function increment($key, $ttl = 60) {
        $exists = $this->redis->exists($key);
        $count = $this->redis->incr($key);
        if (!$exists) {
            $this->redis->expire($key, $ttl);
        }
        return $count;
    }
}
