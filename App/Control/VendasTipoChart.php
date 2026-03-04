<?php
use Nexa\Control\Page;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Container\Panel;

/**
 * Vendas por tipo
 */
class VendasTipoChart extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

	$loader = new \Twig\Loader\FilesystemLoader('App/Resources');
	$twig = new \Twig\Environment($loader);

        try
        {
            $replaces = VendasAnalyticsService::getTypeChartPayload();
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            return;
        }
        
        $content = $twig->render('vendas_tipo.html', $replaces);
        
        // cria um painél para conter o formulário
        $panel = new Panel('Mix de categorias');
        $panel->add($content);
        
        parent::add($panel);
    }
}
