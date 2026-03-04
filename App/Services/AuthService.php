<?php
use Nexa\Session\Session;

class AuthService
{
    public static function boot()
    {
        new Session;
    }

    public static function isLogged()
    {
        self::boot();

        return (bool) Session::getValue('logged');
    }

    public static function attempt($login, $password)
    {
        self::boot();
        RequestThrottleService::enforceLoginLimit();

        $credentials = [
            [
                'login' => getenv('APP_LOGIN') ?: '',
                'hash' => getenv('APP_PASSWORD_HASH') ?: '',
                'mode' => 'admin',
            ],
            [
                'login' => getenv('APP_DEMO_LOGIN') ?: '',
                'hash' => getenv('APP_DEMO_PASSWORD_HASH') ?: '',
                'mode' => 'demo',
            ],
        ];

        foreach ($credentials as $credential)
        {
            if ($credential['login'] === '' || $credential['hash'] === '')
            {
                continue;
            }

            if ($login === $credential['login'] && password_verify($password, $credential['hash']))
            {
                Session::regenerate();
                Session::setValue('logged', true);
                Session::setValue('auth_mode', $credential['mode']);
                Session::setValue('auth_user', $credential['login']);
                return true;
            }
        }

        if ((getenv('APP_LOGIN') ?: '') === '' || (getenv('APP_PASSWORD_HASH') ?: '') === '')
        {
            throw new Exception('Configure APP_LOGIN e APP_PASSWORD_HASH no ambiente para habilitar o login.');
        }

        return false;
    }

    public static function isDemoUser()
    {
        self::boot();

        return Session::getValue('auth_mode') === 'demo';
    }

    public static function hasPublicDemoAccount()
    {
        return self::getDemoDisplayLogin() !== '' && self::getDemoDisplayPassword() !== '';
    }

    public static function getDemoDisplayLogin()
    {
        return getenv('APP_DEMO_DISPLAY_LOGIN') ?: (getenv('APP_DEMO_LOGIN') ?: '');
    }

    public static function getDemoDisplayPassword()
    {
        return getenv('APP_DEMO_DISPLAY_PASSWORD') ?: '';
    }

    public static function logout()
    {
        self::boot();
        Session::freeSession();
    }
}
