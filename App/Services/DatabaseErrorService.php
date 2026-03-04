<?php
class DatabaseErrorService
{
    public static function toUserMessage(Throwable $error, $defaultMessage = 'Operação não pôde ser concluída.')
    {
        $code = (string) $error->getCode();
        $message = strtolower($error->getMessage());

        if ($code === '23503' || strpos($message, 'foreign key') !== false)
        {
            return 'Não é possível excluir este registro porque ele está vinculado a outros dados.';
        }

        if ($code === '23505' || strpos($message, 'unique constraint') !== false)
        {
            return 'Já existe um registro com os mesmos dados informados.';
        }

        return $defaultMessage;
    }
}
