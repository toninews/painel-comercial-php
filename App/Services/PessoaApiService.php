<?php
use Nexa\Database\Transaction;

class PessoaApiService
{
    public static function getById($request)
    {
        $idPessoa = isset($request['id']) ? (int) $request['id'] : 0;
        if ($idPessoa <= 0)
        {
            throw new Exception('ID de pessoa inválido.');
        }

        Transaction::open('painel_comercial');

        try
        {
            $pessoa = Pessoa::find($idPessoa);
            if (!$pessoa)
            {
                throw new Exception("Pessoa {$idPessoa} não encontrada.");
            }

            $data = $pessoa->toArray();
            Transaction::close();

            return $data;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }
}
