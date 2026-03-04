<?php
use Nexa\Control\Page;
use Nexa\Database\Transaction;

class ModelTest1 extends Page
{
    public function show()
    {
        try {
            Transaction::open('painel_comercial');
            
            $c1 = Cidade::find(12);
            
            print($c1->nome) . '<br>';
            print($c1->estado->nome) . '<br>';
            print($c1->nome_estado) . '<br>';
            
            $p1 = Pessoa::find(12);
            
            print($p1->nome) . '<br>';
            print($p1->nome_cidade) . '<br>';
            print($p1->cidade->nome) . '<br>';
            print($p1->cidade->estado->nome) . '<br>';
            
            Transaction::close();
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}