<?php
use Nexa\Control\Page;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Container\Panel;

/**
 * Relatório de vendas
 */
class ProdutosReport extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
		$twig = new \Twig\Environment($loader);

        $replaces = [
            'produtos' => [],
            'total_produtos' => 0,
            'total_estoque' => '0',
        ];
        
        try
        {
            $replaces = array_merge($replaces, ProdutosReportService::build());
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
        
        $content = $twig->render('produtos_report.html', $replaces);
        
        // cria um painél para conter o formulário
        $panel = new Panel('Produtos');
        $panel->add($content);
        parent::add($panel);
    }
}
