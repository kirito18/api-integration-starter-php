<?php
declare(strict_types=1);

namespace App;

final class Config
{
    /** @var array<string,string> */
    private static array $env = [];

    public static function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);


            $value = trim($value, "\"'");

            self::$env[$key] = $value;


            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    public static function get(string $key, ?string $default = null): string
    {
        if (isset(self::$env[$key])) {
            return self::$env[$key];
        }
        $v = getenv($key);
        if ($v !== false) {
            return (string)$v;
        }
        return $default ?? '';
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::get($key, $default ? 'true' : 'false');
        return in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $v = self::get($key, (string)$default);
        return (int)$v;
    }
}