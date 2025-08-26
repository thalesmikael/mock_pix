<?php

declare(strict_types=1);

namespace App\Config;

class Env
{
    private static $cache = [];

    public static function get(string $key, string $default = ''): string
    {
        if (!isset(self::$cache[$key])) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
            self::$cache[$key] = $value;
        }

        return self::$cache[$key];
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, (string) $default);
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) self::get($key, (string) $default);
    }

    public static function getArray(string $key, string $separator = ','): array
    {
        $value = self::get($key, '');
        return $value ? explode($separator, $value) : [];
    }
}
