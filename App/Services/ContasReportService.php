<?php
use Nexa\Database\Criteria;
use Nexa\Database\Repository;
use Nexa\Database\Transaction;

class ContasReportService
{
    public static function build($dataIni = null, $dataFim = null)
    {
        $payload = [
            'contas' => [],
            'report_title' => 'Contas',
            'report_subtitle' => 'Resumo financeiro por vencimento',
            'total_contas' => 0,
            'total_valor' => 0,
        ];

        Transaction::open('painel_comercial');

        try
        {
            $repositorio = new Repository('Conta');
            $criterio = new Criteria;
            $criterio->setProperty('order', 'dt_vencimento');

            if ($dataIni)
            {
                $criterio->add('dt_vencimento', '>=', $dataIni);
            }

            if ($dataFim)
            {
                $criterio->add('dt_vencimento', '<=', $dataFim);
            }

            $contas = $repositorio->load($criterio);

            foreach ((array) $contas as $conta)
            {
                $payload['contas'][] = [
                    'dt_emissao' => $conta->dt_emissao,
                    'dt_vencimento' => $conta->dt_vencimento,
                    'nome_cliente' => $conta->cliente->nome,
                    'valor' => (float) $conta->valor,
                    'paga' => $conta->paga,
                ];
                $payload['total_contas']++;
                $payload['total_valor'] += (float) $conta->valor;
            }

            return $payload;
        }
        finally
        {
            Transaction::close();
        }
    }
}
