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

    public static function forAllForSession(): array
    {
        $keys = self::$redis->keys(self::PREFIX . '*');
        $result = [];

        foreach ($keys as $key) {
            $shortKey = str_replace(self::PREFIX, '', $key);
            $data = self::$redis->get($key);

            if ($data !== null) {
                $result[$shortKey] = [
                    'key' => $shortKey,
                    'data' => unserialize($data),
                    'ttl' => self::$redis->ttl($key),
                    'exists' => true
                ];
            }
        }

        return $result;
    }

    /**
     * Vymaže celou cache z Redis včetně ORM cache
     */
    public static function clearAllCache(): bool
    {
        try {
            // Vymazání celé databáze Redis (flushdb)
            self::$redis->flushdb();

            // Vymazání ORM cache pomocí Doctrine
            self::clearDoctrineCache();

            return true;
        } catch (\Exception $e) {
            error_log("Chyba při vymazání Redis cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vymaže pouze Doctrine ORM cache
     */
    public static function clearDoctrineCache(): void
    {
        try {
            // Získání EntityManager z containeru
            $em = \Core\Facades\Container::get('doctrine.em');
            if ($em) {
                $config = $em->getConfiguration();

                // Vymazání metadata cache
                if ($config->getMetadataCache()) {
                    $config->getMetadataCache()->clear();
                }

                // Vymazání query cache
                if ($config->getQueryCache()) {
                    $config->getQueryCache()->clear();
                }

                // Vymazání result cache
                if ($config->getResultCache()) {
                    $config->getResultCache()->clear();
                }
            }
        } catch (\Exception $e) {
            error_log("Chyba při vymazání Doctrine cache: " . $e->getMessage());
        }
    }

    /**
     * Vymaže pouze session cache (container data)
     */
    public static function clearSessionCache(): void
    {
        $keys = self::$redis->keys(self::PREFIX . '*');
        if (!empty($keys)) {
            self::$redis->del($keys);
        }
    }

    /**
     * Vymaže cache podle patternu
     */
    public static function clearCacheByPattern(string $pattern): void
    {
        $keys = self::$redis->keys($pattern);
        if (!empty($keys)) {
            self::$redis->del($keys);
        }
    }
}

