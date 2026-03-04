<?php
use Nexa\Database\Criteria;
use Nexa\Database\Repository;
use Nexa\Database\Transaction;

class PessoaListingService
{
    public static function search($name = null, $limit = null, $offset = 0)
    {
        Transaction::open('painel_comercial');

        try
        {
            $repository = new Repository('Pessoa');
            $criteria = new Criteria;
            $criteria->setProperty('order', 'id');

            if ($name)
            {
                $criteria->add('nome', 'like', "%{$name}%");
            }

            $count = $repository->count($criteria);

            if ($limit !== null)
            {
                $criteria->setProperty('limit', (int) $limit);
                $criteria->setProperty('offset', (int) $offset);
            }

            $items = $repository->load($criteria);

            if ($items)
            {
                foreach ($items as $item)
                {
                    $item->nome_cidade = $item->nome_cidade;
                }
            }

            return [
                'items' => $items,
                'count' => $count,
            ];
        }
        finally
        {
            Transaction::close();
        }
    }
}
