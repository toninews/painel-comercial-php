<?php
use Nexa\Database\Criteria;
use Nexa\Database\Repository;
use Nexa\Database\Transaction;

class ProdutoListingService
{
    public static function load(array $filters = [])
    {
        Transaction::open('painel_comercial');

        try
        {
            $repository = new Repository('Produto');
            $criteria = new Criteria;
            $criteria->setProperty('order', 'id');

            foreach ($filters as $filter)
            {
                $criteria->add($filter[0], $filter[1], $filter[2], $filter[3]);
            }

            $items = $repository->load($criteria) ?: [];

            foreach ($items as $item)
            {
                $item->nome_fabricante = $item->nome_fabricante;
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
