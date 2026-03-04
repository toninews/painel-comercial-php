<?php
use Nexa\Database\Transaction;

class PessoaFormOptionsService
{
    public static function load()
    {
        Transaction::open('painel_comercial');

        try
        {
            return [
                'cidades' => self::mapCollection(Cidade::all()),
                'grupos' => self::mapCollection(Grupo::all()),
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
