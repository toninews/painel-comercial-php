<?php
use Nexa\Database\Transaction;

class CidadeApplicationService
{
    public static function save($dados)
    {
        Transaction::open('painel_comercial');

        try
        {
            $cidadeData = (array) $dados;
            $cidade = self::resolveCidade($cidadeData);
            $cidade->fromArray($cidadeData);
            $cidade->store();

            Transaction::close();

            return $cidade;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    public static function loadForEdit($id)
    {
        Transaction::open('painel_comercial');

        try
        {
            $cidade = Cidade::find($id);

            if (!$cidade)
            {
                throw new Exception('Cidade não encontrada.');
            }

            Transaction::close();

            return $cidade;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    public static function delete($id)
    {
        Transaction::open('painel_comercial');

        try
        {
            $cidade = Cidade::find($id);

            if (!$cidade)
            {
                throw new Exception('Registro não encontrado.');
            }

            $cidade->delete();
            Transaction::close();
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    private static function resolveCidade(array $cidadeData)
    {
        $id = isset($cidadeData['id']) ? (int) $cidadeData['id'] : 0;

        if ($id > 0)
        {
            $cidade = Cidade::find($id);
            if ($cidade)
            {
                return $cidade;
            }
        }

        return new Cidade;
    }
}
