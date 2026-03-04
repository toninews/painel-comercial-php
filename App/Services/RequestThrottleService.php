<?php
class RequestThrottleService
{
    public static function enforceLoginLimit()
    {
        $maxAttempts = (int) (getenv('APP_LOGIN_RATE_LIMIT_MAX') ?: 10);
        $windowSeconds = (int) (getenv('APP_LOGIN_RATE_LIMIT_WINDOW') ?: 900);

        self::enforce(
            self::buildKey('login-ip', [], true, false),
            $maxAttempts,
            $windowSeconds,
            'Muitas tentativas de login. Tente novamente em alguns minutos.'
        );

        self::enforce(
            self::buildKey('login-session', [], false, true),
            $maxAttempts,
            $windowSeconds,
            'Muitas tentativas de login. Tente novamente em alguns minutos.'
        );
    }

    public static function enforceDemoWriteLimit($class, $method)
    {
        $maxAttempts = (int) (getenv('APP_DEMO_WRITE_LIMIT_MAX') ?: 20);
        $windowSeconds = (int) (getenv('APP_DEMO_WRITE_LIMIT_WINDOW') ?: 3600);

        self::enforce(
            self::buildKey('demo-write-ip', [], true, false),
            $maxAttempts,
            $windowSeconds,
            'Limite temporário de alterações atingido para este IP no modo demo.'
        );

        self::enforce(
            self::buildKey('demo-write-session', [], false, true),
            (int) (getenv('APP_DEMO_SESSION_WRITE_LIMIT_MAX') ?: $maxAttempts),
            $windowSeconds,
            'Limite temporário de alterações atingido para esta sessão no modo demo.'
        );

        self::enforce(
            self::buildKey('demo-write-route', [$class, $method], true, false),
            $maxAttempts,
            $windowSeconds,
            'Limite temporário de alterações atingido para este IP no modo demo.'
        );
    }

    public static function enforceDemoCreateLimit($class)
    {
        $maxAttempts = (int) (getenv('APP_DEMO_CREATE_LIMIT_MAX') ?: 8);
        $windowSeconds = (int) (getenv('APP_DEMO_CREATE_LIMIT_WINDOW') ?: 3600);

        self::enforce(
            self::buildKey('demo-create-ip', [$class], true, false),
            $maxAttempts,
            $windowSeconds,
            'Limite temporário de novos cadastros atingido para este IP no modo demo.'
        );

        self::enforce(
            self::buildKey('demo-create-session', [$class], false, true),
            (int) (getenv('APP_DEMO_SESSION_CREATE_LIMIT_MAX') ?: $maxAttempts),
            $windowSeconds,
            'Limite temporário de novos cadastros atingido para esta sessão no modo demo.'
        );
    }

    public static function isWriteMethod($method)
    {
        $writeMethods = [
            'onSave',
            'onDelete',
            'onDeleteConfirmed',
            'onAdiciona',
            'onGravaVenda',
            'onLogout',
        ];

        return in_array($method, $writeMethods, true);
    }

    public static function isCreateOperation($method, array $parameters)
    {
        if ($method !== 'onSave')
        {
            return false;
        }

        $id = isset($parameters['id']) ? trim((string) $parameters['id']) : '';
        $key = isset($parameters['key']) ? trim((string) $parameters['key']) : '';

        return $id === '' && $key === '';
    }

    private static function enforce($key, $maxAttempts, $windowSeconds, $message)
    {
        if ($maxAttempts < 1 || $windowSeconds < 1)
        {
            return;
        }

        $directory = self::getDirectory();
        $file = $directory . '/' . sha1($key) . '.json';
        $now = time();
        $attempts = [];

        if (is_file($file))
        {
            $decoded = json_decode((string) file_get_contents($file), true);
            $attempts = is_array($decoded) ? $decoded : [];
        }

        $attempts = array_values(array_filter($attempts, function ($timestamp) use ($now, $windowSeconds) {
            return is_int($timestamp) && ($timestamp > ($now - $windowSeconds));
        }));

        if (count($attempts) >= $maxAttempts)
        {
            throw new Exception($message);
        }

        $attempts[] = $now;
        file_put_contents($file, json_encode($attempts));
    }

    private static function buildKey($scope, array $segments = [], $includeIp = true, $includeSession = true)
    {
        $base = [$scope];

        if ($includeIp)
        {
            $base[] = self::getClientIp();
        }

        if ($includeSession)
        {
            $base[] = self::getSessionIdentifier();
        }

        return implode(':', array_merge($base, $segments));
    }

    private static function getDirectory()
    {
        $directory = getcwd() . '/tmp/rate_limits';

        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
        }

        if (!is_writable($directory))
        {
            @chmod($directory, 0777);
        }

        return $directory;
    }

    private static function getClientIp()
    {
        $forwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim((string) $_SERVER['HTTP_X_FORWARDED_FOR']) : '';
        if ($forwardedFor !== '')
        {
            $parts = explode(',', $forwardedFor);
            return trim($parts[0]);
        }

        return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    }

    private static function getSessionIdentifier()
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            $sessionId = session_id();
            if ($sessionId !== '')
            {
                return $sessionId;
            }
        }

        if (isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] !== '')
        {
            return (string) $_COOKIE[session_name()];
        }

        return 'no-session';
    }
}
