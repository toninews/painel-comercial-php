<?php
class AppConfig
{
    private static $config;

    public static function get($key = null, $default = null)
    {
        if (!self::$config) {
            self::$config = require 'App/Config/app.php';
        }

        if ($key === null) {
            return self::$config;
        }

        return self::$config[$key] ?? $default;
    }
}
