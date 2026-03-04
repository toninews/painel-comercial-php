<?php
use Nexa\Database\Transaction;

class ProdutoApplicationService
{
    public static function save($dados)
    {
        Transaction::open('painel_comercial');

        try
        {
            $produtoData = (array) $dados;
            $produto = self::resolveProduto($produtoData);
            $produto->fromArray($produtoData);
            $produto->store();

            Transaction::close();

            return $produto;
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
            $produto = Produto::find($id);

            if (!$produto)
            {
                throw new Exception('Produto não encontrado.');
            }

            Transaction::close();

            return $produto;
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
            $produto = Produto::find($id);

            if (!$produto)
            {
                throw new Exception('Registro não encontrado.');
            }

            $produto->delete();
            Transaction::close();
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    private static function resolveProduto(array $produtoData)
    {
        $id = isset($produtoData['id']) ? (int) $produtoData['id'] : 0;

        if ($id > 0)
        {
            $produto = Produto::find($id);
            if ($produto)
            {
                return $produto;
            }
        }

        return new Produto;
    }
}
