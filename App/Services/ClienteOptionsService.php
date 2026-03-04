<?php
use Nexa\Database\Transaction;

class ClienteOptionsService
{
    public static function load()
    {
        Transaction::open('painel_comercial');

        try
        {
            $options = [];

            foreach (Pessoa::all() as $pessoa)
            {
                $options[$pessoa->id] = $pessoa->nome;
            }

            asort($options);

            return $options;
        }
        finally
        {
            Transaction::close();
        }
    }
}
