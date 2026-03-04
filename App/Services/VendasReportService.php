<?php
use Nexa\Database\Criteria;
use Nexa\Database\Repository;
use Nexa\Database\Transaction;

class VendasReportService
{
    public static function build($dataIni = null, $dataFim = null)
    {
        $payload = [
            'vendas' => [],
            'report_title' => 'Vendas',
            'report_subtitle' => 'Resumo comercial com itens vendidos',
            'total_vendas' => 0,
            'total_faturado' => 0,
        ];

        Transaction::open('painel_comercial');

        try
        {
            $repositorio = new Repository('Venda');
            $criterio = new Criteria;
            $criterio->setProperty('order', 'data_venda');

            if ($dataIni)
            {
                $criterio->add('data_venda', '>=', $dataIni);
            }

            if ($dataFim)
            {
                $criterio->add('data_venda', '<=', $dataFim);
            }

            $vendas = $repositorio->load($criterio);

            foreach ((array) $vendas as $venda)
            {
                $vendaData = [
                    'nome_cliente' => $venda->cliente->nome,
                    'data_venda' => $venda->data_venda,
                    'valor_final' => (float) $venda->valor_final,
                    'parcelas' => isset($venda->parcelas) ? (int) $venda->parcelas : null,
                    'itens' => [],
                ];

                if (!$vendaData['parcelas'])
                {
                    $vendaData['parcelas'] = self::estimateParcelas($venda);
                }

                foreach ((array) $venda->itens as $item)
                {
                    $vendaData['itens'][] = [
                        'id_produto' => $item->id_produto,
                        'descricao' => $item->produto->descricao,
                        'quantidade' => (float) $item->quantidade,
                        'preco' => (float) $item->preco,
                    ];
                }

                $payload['vendas'][] = $vendaData;
                $payload['total_vendas']++;
                $payload['total_faturado'] += (float) $venda->valor_final;
            }

            return $payload;
        }
        finally
        {
            Transaction::close();
        }
    }

    private static function estimateParcelas($venda)
    {
        $conn = Transaction::get();
        $statement = $conn->prepare('
            SELECT COUNT(*) AS parcelas
              FROM conta
             WHERE id_cliente = :id_cliente
               AND dt_emissao = :data_venda
        ');
        $statement->execute([
            ':id_cliente' => (int) $venda->id_cliente,
            ':data_venda' => $venda->data_venda,
        ]);

        $parcelas = (int) $statement->fetchColumn();

        return $parcelas > 0 ? $parcelas : null;
    }
}
