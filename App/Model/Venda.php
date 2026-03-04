<?php
use Nexa\Database\Transaction;
use Nexa\Database\Record;
use Nexa\Database\Repository;
use Nexa\Database\Criteria;

class Venda extends Record
{
    const TABLENAME = 'venda';
    private $itens;
    private $cliente;
    
    /**
     * Atribui o cliente
     */
    public function set_cliente(Pessoa $c)
    {
        $this->cliente = $c;
        $this->id_cliente = $c->id;
    }
    
    /**
     * retorna o objeto cliente vinculado à venda
     */
    public function get_cliente()
    {
        if (empty($this->cliente))
        {
            $this->cliente = new Pessoa($this->id_cliente);
        }
        
        // Retorna o objeto instanciado
        return $this->cliente;
    }
    
    /**
     * Adiciona um item (produto) à venda
     */
    public function addItem(Produto $p, $quantidade)
    {    
        $item = new ItemVenda;
        $item->produto    = $p;
        $item->preco      = $p->preco_venda;
        $item->quantidade = $quantidade;
        
        $this->itens[] = $item;
    }
    
    /**
     * Armazena uma venda e seus itens no banco de dados
     */
    public function store()
    {
        $this->recalculateTotals();

        // armazena a venda
        parent::store();
        // percorre os itens da venda
        foreach ((array) $this->itens as $item)
        {
            $item->id_venda = $this->id;
            // armazena o item
            $item->store();
        }
    }
    
    /**
     * Retorna os itens da venda
     */
    public function get_itens()
    {
        // instancia um repositóio de Item
        $repositorio = new Repository('ItemVenda');
        // define o critério
        $criterio = new Criteria;
        $criterio->add('id_venda', '=', $this->id);
        // carrega a coleção
        $this->itens = $repositorio->load($criterio);
        // retorna os itens
        return $this->itens;
    }
    
    /**
     * Retorna vendas por mes
     */
    public static function getVendasMes()
    {
        $meses = array();
        $meses[1] = 'Janeiro';
        $meses[2] = 'Fevereiro';
        $meses[3] = 'Março';
        $meses[4] = 'Abril';
        $meses[5] = 'Maio';
        $meses[6] = 'Junho';
        $meses[7] = 'Julho';
        $meses[8] = 'Agosto';
        $meses[9] = 'Setembro';
        $meses[10] = 'Outubro';
        $meses[11] = 'Novembro';
        $meses[12] = 'Dezembro';
        
        $conn = Transaction::get();
        $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $sql = 'SELECT EXTRACT(MONTH FROM data_venda) AS mes, SUM(valor_final) AS valor
                    FROM venda
                    GROUP BY 1';
        }
        else {
            $sql = "SELECT strftime('%m', data_venda) AS mes, SUM(valor_final) AS valor
                    FROM venda
                    GROUP BY 1";
        }

        $statement = $conn->prepare($sql);
        $statement->execute();
        
        $dataset = [];
        foreach ($statement as $row)
        {
            $mes = $meses[ (int) $row['mes'] ];
            $dataset[ $mes ] = $row['valor'];
        }
        
        return $dataset;
    }
    
    /**
     * Retorna vendas por mes
     */
    public static function getVendasTipo()
    {
        $conn = Transaction::get();
        $sql = 'SELECT tipo.nome AS tipo, SUM(item_venda.quantidade * item_venda.preco) AS total
                FROM venda
                INNER JOIN item_venda ON venda.id = item_venda.id_venda
                INNER JOIN produto ON item_venda.id_produto = produto.id
                INNER JOIN tipo ON produto.id_tipo = tipo.id
                GROUP BY tipo.nome';
        $statement = $conn->prepare($sql);
        $statement->execute();
        
        $dataset = [];
        foreach ($statement as $row)
        {
            $dataset[ $row['tipo'] ] = $row['total'];
        }
        
        return $dataset;
    }

    private function recalculateTotals()
    {
        $subtotal = 0.0;

        foreach ((array) $this->itens as $item)
        {
            $subtotal += ((float) $item->preco * (float) $item->quantidade);
        }

        $desconto = isset($this->desconto) ? (float) $this->desconto : 0.0;
        $acrescimos = isset($this->acrescimos) ? (float) $this->acrescimos : 0.0;

        $this->valor_venda = $subtotal;
        $this->valor_final = max(0, $subtotal + $acrescimos - $desconto);
    }
}
