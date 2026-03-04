<?php
use Nexa\Database\Criteria;
use Nexa\Database\Repository;
use Nexa\Database\Transaction;

class CidadeListingService
{
    public static function load()
    {
        Transaction::open('painel_comercial');

        try
        {
            $repository = new Repository('Cidade');
            $criteria = new Criteria;
            $criteria->setProperty('order', 'id');

            $items = $repository->load($criteria) ?: [];

            foreach ($items as $item)
            {
                $item->nome_estado = $item->nome_estado;
            }

            Transaction::close();

            return $items;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }
}
