<?php
namespace Nexa\Session;

/**
 * Gerencia o registro da seção
 */
class Session
{
    /**
     * inicializa uma seção
     */
    public function __construct()
    {
        if (!session_id())
        {
            $sessionPath = getcwd() . '/tmp/sessions';

            if (!is_dir($sessionPath))
            {
                mkdir($sessionPath, 0777, true);
            }

            if (!is_writable($sessionPath))
            {
                @chmod($sessionPath, 0777);
            }

            session_save_path($sessionPath);
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            session_start();
        }
    }

    /**
     * Armazena uma variável na seção
     * @param $var     = Nome da variável
     * @param $value = Valor
     */
    public static function setValue($var, $value)
    {
        $_SESSION[$var] = $value;
    }

    /**
     * Retorna uma variável da seção
     * @param $var = Nome da variável
     */
    public static function getValue($var)
    {
        if (isset($_SESSION[$var]))
        {
            return $_SESSION[$var];
        }
    }

    /**
     * Destrói os dados de uma seção
     */
    public static function freeSession()
    {
        $_SESSION = array();
        session_destroy();
    }

    public static function regenerate()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
