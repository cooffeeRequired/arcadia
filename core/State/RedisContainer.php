<?php

namespace Core\State;

use Predis\Client;

final class RedisContainer
{
    private static ?Client $redis = null;
    private const string PREFIX = 'container:';

    public static function init(Client $client): void
    {
        self::$redis = $client;
    }

    public static function set(string $key, mixed $value, int $ttl = 0): void
    {
        $data = serialize($value);
        $fullKey = self::PREFIX . $key;

        if ($ttl > 0) {
            self::$redis->setex($fullKey, $ttl, $data);
        } else {
            self::$redis->set($fullKey, $data);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $fullKey = self::PREFIX . $key;
        $data = self::$redis->get($fullKey);

        return $data !== null ? unserialize($data) : $default;
    }

    public static function forget(string $key): void
    {
        self::$redis->del([self::PREFIX . $key]);
    }

    public static function all(): array
    {
        $keys = self::$redis->keys(self::PREFIX . '*');
        $result = [];

        foreach ($keys as $key) {
            $shortKey = str_replace(self::PREFIX, '', $key);
            $result[$shortKey] = unserialize(self::$redis->get($key));
        }

        return $result;
    }
}

