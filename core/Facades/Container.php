<?php

namespace Core\Facades;

use Exception;
use RuntimeException;

class Container
{
    protected static array $services = [];

    public static function set(string $key, mixed $service, ?array $aliases = []): void
    {
        if (is_array($aliases)) {
            self::alias($aliases, $service);
        }
        self::$services[$key] = $service;
    }

    public static function alias(?array $aliases, $value): void
    {
        foreach ($aliases as $alias) {
            self::$services[$alias] = $value;
        }
    }

    /**
     * @template T of object
     * @param class-string<T>|null $castTo
     * @param string $key
     * @return T|mixed
     * @IgnoredException
     */
    public static function get(string $key, ?string $castTo = null): mixed
    {
        $service = self::$services[$key] ?? throw new RuntimeException("Service '$key' not found.");

        if (!is_null($castTo) && !$service instanceof $castTo) {
            throw new RuntimeException("Service '$key' is not instance of '$castTo'.");
        }

        return $service;
    }
}