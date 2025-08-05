<?php

namespace Core\Facades;

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

    public static function get(string $key): mixed
    {
        return self::$services[$key] ?? throw new \RuntimeException("Service '$key' not found.");
    }
}