<?php
use Nexa\Control\Page;
use Nexa\Widgets\Base\Element;
use Nexa\Widgets\Container\HBox;
use Nexa\Widgets\Container\Panel;
use Nexa\Widgets\Dialog\Message;

/**
 * Vendas por mês
 */
class DashboardView extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        try
        {
            $analytics = VendasAnalyticsService::getDashboardMetrics();
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            return;
        }

        $hero = new Panel('Visão Geral');
        $heroContent = new Element('div');
        $heroContent->class = 'dashboard-hero';
        $heroContent->add("
            <div class=\"dashboard-hero__content\">
                <span class=\"dashboard-hero__eyebrow\">Painel executivo</span>
                <h2>Resumo comercial com leitura rápida do negócio.</h2>
                <p>Este dashboard consolida a distribuição do faturamento por período e por categoria para apresentar o projeto de forma mais próxima de um produto real.</p>
            </div>
        ");

        $metrics = new Element('div');
        $metrics->class = 'dashboard-metrics';
        $metrics->add($this->renderMetric('Faturamento total', 'R$ ' . number_format($analytics['total_faturado'], 2, ',', '.'), 'Soma das vendas registradas na base demo.'));
        $metrics->add($this->renderMetric('Melhor mês', $analytics['melhor_mes'], 'Mês com maior volume agregado.'));
        $metrics->add($this->renderMetric('Categoria líder', $analytics['principal_categoria'], 'Tipo com maior participação nas vendas.'));
        $metrics->add($this->renderMetric('Média por categoria', 'R$ ' . number_format($analytics['ticket_categorias'], 2, ',', '.'), 'Indicador rápido para comparar o mix.'));
        $heroContent->add($metrics);
        $hero->add($heroContent);

        $charts = new HBox;
        $charts->class = 'dashboard-grid';
        $charts->add(new VendasMesChart)->style .= ';width:49%;vertical-align:top;';
        $charts->add(new VendasTipoChart)->style .= ';width:49%;vertical-align:top;';

        parent::add($hero);
        parent::add($charts);
    }

    private function renderMetric($label, $value, $description)
    {
        return "
            <article class=\"dashboard-metric-card\">
                <span>{$label}</span>
                <strong>{$value}</strong>
                <small>{$description}</small>
            </article>
        ";
    }
}
