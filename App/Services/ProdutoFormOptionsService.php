<?php
use Nexa\Database\Transaction;

class ProdutoFormOptionsService
{
    public static function load()
    {
        Transaction::open('painel_comercial');

        try
        {
            return [
                'fabricantes' => self::mapCollection(Fabricante::all()),
                'tipos' => self::mapCollection(Tipo::all()),
                'unidades' => self::mapCollection(Unidade::all()),
            ];
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
