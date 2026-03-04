<?php
use Nexa\Database\Record;
use Nexa\Database\Criteria;
use Nexa\Database\Repository;

class Conta extends Record
{
    const TABLENAME = 'conta';
	private $cliente;
	
    public function get_cliente()
    {
        if (empty($this->cliente))
        {
            $this->cliente = new Pessoa($this->id_cliente);
        }
        
        // Retorna o objeto instanciado
        return $this->cliente;
    }
    
	public static function getByPessoa($id_pessoa)
	{
	    $criteria = new Criteria;
	    $criteria->add('paga', '<>', 'S');
	    $criteria->add('id_cliente', '=', $id_pessoa);
	    
	    $repo = new Repository('Conta');
	    return $repo->load($criteria);
	}
	
	public static function debitosPorPessoa($id_pessoa)
	{
	    $total = 0;
	    $contas = self::getByPessoa($id_pessoa);
	    if ($contas)
	    {
	        foreach ($contas as $conta)
	        {
	            $total += $conta->valor;
	        }
	    }
	    return $total;
	}
	
	public static function geraParcelas($id_cliente, $delay, $valor, $parcelas)
	{
	    if ($parcelas < 1)
	    {
	        throw new Exception('Quantidade de parcelas invalida');
	    }

	    $date = new DateTime(date('Y-m-d'));
	    $date->add(new DateInterval('P'.$delay.'D'));

	    $valorTotalCentavos = (int) round($valor * 100);
	    $valorBaseCentavos = intdiv($valorTotalCentavos, $parcelas);
	    $restanteCentavos = $valorTotalCentavos % $parcelas;

	    for ($n=1; $n<=$parcelas; $n++)
	    {
	        $conta = new self;
	        $conta->id_cliente = $id_cliente;
	        $conta->dt_emissao = date('Y-m-d');
	        $conta->dt_vencimento = $date->format('Y-m-d');
	        $valorParcelaCentavos = $valorBaseCentavos;
	        if ($restanteCentavos > 0)
	        {
	            $valorParcelaCentavos++;
	            $restanteCentavos--;
	        }
	        $conta->valor = $valorParcelaCentavos / 100;
	        $conta->paga = 'N';
	        $conta->store();
	        
	        $date->add(new DateInterval('P1M'));
	    }
	}
}
