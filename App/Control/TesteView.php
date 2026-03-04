<?php
use Nexa\Control\Page;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Container\Panel;

class TesteView extends Page
{
    public function __construct()
    {
        parent::__construct();

        $count = 0;
        $previewItems = [];
        try
        {
            $diagnostico = SaldoDiagnosticoService::build(5);
            $count = (int) $diagnostico['count'];
            $previewItems = (array) $diagnostico['preview_items'];
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
        $preview = '';

        if ($previewItems)
        {
            $preview .= '<div class="report-table"><table class="table table-striped table-hover">';
            $preview .= '<thead><tr><th>Pessoa</th><th>Saldo</th></tr></thead><tbody>';

            foreach ($previewItems as $item)
            {
                $nome = isset($item->nome) ? $item->nome : 'Sem nome';
                $saldo = isset($item->saldo) ? number_format((float) $item->saldo, 2, ',', '.') : '0,00';
                $preview .= "<tr><td>{$nome}</td><td>R$ {$saldo}</td></tr>";
            }

            $preview .= '</tbody></table></div>';
        }
        else
        {
            $preview = '<div class="report-table__empty">Nenhum registro encontrado na view de saldo.</div>';
        }

        $panel = new Panel('Diagnóstico da view de saldos');
        $panel->add("
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Diagnóstico</span>
                    <h2>Leitura da view de saldos sem debug bruto.</h2>
                    <p>Esta tela agora serve como verificação simples de integração com a view `view_saldo_pessoa`, sem usar `var_dump`.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Registros encontrados</span>
                    <strong>{$count}</strong>
                    <small>Total retornado pela view de saldos no momento da consulta.</small>
                </article>
            </section>
            {$preview}
        ");

        parent::add($panel);
    }
}
