<?php
use Nexa\Database\Transaction;
use Nexa\Session\Session;

class VendaDraftService
{
    public static function initialize()
    {
        new Session;
    }

    public static function addProductById($productId, $quantity)
    {
        Transaction::open('painel_comercial');

        try
        {
            $produto = Produto::find($productId);

            if (!$produto)
            {
                throw new Exception('Produto não encontrado.');
            }

            $item = new StdClass;
            $item->id_produto = $produto->id;
            $item->quantidade = (float) $quantity;
            $item->descricao = $produto->descricao;
            $item->preco = $produto->preco_venda;

            VendaSessionService::add($item);
        }
        finally
        {
            Transaction::close();
        }
    }

    public static function summary()
    {
        return [
            'count' => VendaSessionService::count(),
            'total' => VendaSessionService::total(),
        ];
    }
}
