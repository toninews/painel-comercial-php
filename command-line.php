<?php
require_once 'bootstrap.php';

use Nexa\Database\Transaction;

try
{
    Transaction::open('painel_comercial');
    var_dump( Pessoa::find(1)->toArray() );
    Transaction::close();
}
catch (Exception $e)
{
    echo $e->getMessage();
}
