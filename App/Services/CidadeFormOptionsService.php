<?php
use Nexa\Database\Transaction;

class CidadeFormOptionsService
{
    public static function loadEstados()
    {
        Transaction::open('painel_comercial');

        try
        {
            return self::mapCollection(Estado::all());
        }
        finally
        {
            Transaction::close();
        }
    }

    private static function mapCollection($items)
    {
        $mapped = [];

        foreach ($items as $item)
        {
            $mapped[$item->id] = $item->nome;
        }

        return $mapped;
    }
}
