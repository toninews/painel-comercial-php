<?php
namespace Nexa\Control;

use ReflectionMethod;

class RouteGuard
{
    public static function isValidClassName($class)
    {
        return is_string($class) && preg_match('/^[A-Z][A-Za-z0-9_]*$/', $class);
    }

    public static function isAllowedPageClass($class)
    {
        if (!self::isValidClassName($class)) {
            return false;
        }

        $file = "App/Control/{$class}.php";

        if (!file_exists($file) || !class_exists($class)) {
            return false;
        }

        $diagnosticClasses = ['SessionTest', 'TesteView', 'ViewSource'];
        $diagnosticsEnabled = (getenv('APP_ENABLE_DIAGNOSTICS') ?: '0') === '1';
        if (in_array($class, $diagnosticClasses, true) && !$diagnosticsEnabled) {
            return false;
        }

        return true;
    }

    public static function isAllowedServiceClass($class)
    {
        if (!self::isValidClassName($class)) {
            return false;
        }

        $file = "App/Services/{$class}.php";

        return file_exists($file) && class_exists($class);
    }

    public static function isAllowedPageMethod($object, $method)
    {
        if (!is_object($object) || !is_string($method) || $method === '') {
            return false;
        }

        if (!method_exists($object, $method)) {
            return false;
        }

        if ($method !== 'show' && strpos($method, 'on') !== 0) {
            return false;
        }

        $reflectionMethod = new ReflectionMethod($object, $method);

        return $reflectionMethod->isPublic() && !$reflectionMethod->isStatic();
    }

    public static function isAllowedStaticMethod($class, $method)
    {
        if (!self::isAllowedServiceClass($class) || !is_string($method) || $method === '') {
            return false;
        }

        if (!method_exists($class, $method)) {
            return false;
        }

        if (strpos($method, 'get') !== 0) {
            return false;
        }

        $reflectionMethod = new ReflectionMethod($class, $method);

        return $reflectionMethod->isPublic() && $reflectionMethod->isStatic();
    }
}
