<?php
use Nexa\Control\Page;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Container\Panel;

/**
 * Relatório de vendas
 */
class PessoasReport extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
	$twig = new \Twig\Environment($loader);

        // vetor de parâmetros para o template
        $replaces = [
            'report_title' => 'Pessoas',
            'report_subtitle' => 'Posicao de saldo por cadastro',
            'total_pessoas' => 0,
            'total_aberto' => 0,
            'pessoas' => [],
        ];
        
        try
        {
            $replaces = array_merge($replaces, PessoasReportService::build());
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
        
        $content = $twig->render('pessoas_report.html', $replaces);
        
        // cria um painél para conter o formulário
        $panel = new Panel('Pessoas');
        $panel->add($content);
        
        parent::add($panel);
    }
}
