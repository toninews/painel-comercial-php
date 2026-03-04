<?php
use Nexa\Database\Criteria;
use Nexa\Database\Repository;
use Nexa\Database\Transaction;

class FabricanteListingService
{
    public static function load()
    {
        Transaction::open('painel_comercial');

        try
        {
            $repository = new Repository('Fabricante');
            $criteria = new Criteria;
            $criteria->setProperty('order', 'id');

            $items = $repository->load($criteria) ?: [];

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
