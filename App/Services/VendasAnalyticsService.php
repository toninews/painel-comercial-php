<?php
use Nexa\Database\Transaction;

class VendasAnalyticsService
{
    public static function getDashboardMetrics()
    {
        Transaction::open('painel_comercial');

        try
        {
            $vendasMes = Venda::getVendasMes();
            $vendasTipo = Venda::getVendasTipo();

            $totalFaturado = array_sum($vendasMes);
            $ticketCategorias = count($vendasTipo) > 0 ? ($totalFaturado / count($vendasTipo)) : 0;
            $melhorMes = !empty($vendasMes) ? array_search(max($vendasMes), $vendasMes) : 'Sem dados';
            $principalCategoria = !empty($vendasTipo) ? array_search(max($vendasTipo), $vendasTipo) : 'Sem dados';

            return [
                'vendas_mes' => $vendasMes,
                'vendas_tipo' => $vendasTipo,
                'total_faturado' => $totalFaturado,
                'ticket_categorias' => $ticketCategorias,
                'melhor_mes' => $melhorMes,
                'principal_categoria' => $principalCategoria,
            ];
        }
        finally
        {
            Transaction::close();
        }
    }

    public static function getMonthlyChartPayload()
    {
        Transaction::open('painel_comercial');

        try
        {
            $vendas = Venda::getVendasMes();

            return [
                'title' => 'Vendas por mês',
                'labels' => json_encode(array_keys($vendas)),
                'data' => json_encode(array_values($vendas)),
                'chart_id' => 'chart-vendas-mes',
                'chart_total' => 'R$ ' . number_format(array_sum($vendas), 2, ',', '.'),
                'chart_caption' => 'Distribuição mensal do faturamento consolidado.',
            ];
        }
        finally
        {
            Transaction::close();
        }
    }

    public static function getTypeChartPayload()
    {
        Transaction::open('painel_comercial');

        try
        {
            $vendas = Venda::getVendasTipo();

            return [
                'title' => 'Vendas por tipo',
                'labels' => json_encode(array_keys($vendas)),
                'data' => json_encode(array_values($vendas)),
                'chart_id' => 'chart-vendas-tipo',
                'chart_total' => 'R$ ' . number_format(array_sum($vendas), 2, ',', '.'),
                'chart_caption' => 'Participação por categoria de produto.',
            ];
        }
        finally
        {
            Transaction::close();
        }
    }
}
