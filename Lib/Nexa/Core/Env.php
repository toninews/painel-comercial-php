<?php
namespace Nexa\Core;

class Env
{
    public static function load($file)
    {
        if (!is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line)
        {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $position = strpos($line, '=');

            if ($position === false) {
                continue;
            }

            $name = trim(substr($line, 0, $position));
            $value = trim(substr($line, $position + 1));

            if ($name === '' || getenv($name) !== false) {
                continue;
            }

            $value = self::normalizeValue($value);

            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    private static function normalizeValue($value)
    {
        $length = strlen($value);

        if ($length >= 2) {
            $firstChar = $value[0];
            $lastChar  = $value[$length - 1];

            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === '\'' && $lastChar === '\'')) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }
}
