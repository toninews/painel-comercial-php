<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Date;

use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

/**
 * Relatório de vendas
 */
class VendasReport extends Page
{
    private $form;   // formulário de entrada
    private $saleSaved = false;

    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $this->saleSaved = isset($_GET['sale_saved']) && $_GET['sale_saved'] === '1';

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_relat_vendas'));
        $this->form->setTitle('Relatório de vendas');
        
        // cria os campos do formulário
        $data_ini = new Date('data_ini');
        $data_fim = new Date('data_fim');
        
        $this->form->addField('Data Inicial', $data_ini, '50%');
        $this->form->addField('Data Final', $data_fim, '50%');
        $this->form->addAction('Gerar', new Action(array($this, 'onGera')));
        
        parent::add($this->form);
    }

    /**
     * Gera o relatório, baseado nos parâmetros do formulário
     */
    public function onGera()
    {
        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
	$twig = new \Twig\Environment($loader);

        // obtém os dados do formulário
        $dados = $this->form->getData();

        // joga os dados de volta ao formulário
        $this->form->setData($dados);
        
        // lê os campos do formulário, converte para o padrão americano
        $data_ini = $dados->data_ini;
        $data_fim = $dados->data_fim;
        
        $replaces = array();
        $replaces['data_ini'] = $dados->data_ini;
        $replaces['data_fim'] = $dados->data_fim;
        $replaces['sale_saved'] = $this->saleSaved;
        
        try
        {
            $replaces = array_merge($replaces, VendasReportService::build($data_ini, $data_fim));
        }
        catch (Exception $e)
        {
            $replaces['report_error'] = $e->getMessage();
        }
        $content = $twig->render('vendas_report.html', $replaces);
        
        $title = 'Vendas';
        $title.= (!empty($dados->data_ini)) ? ' de '  . $dados->data_ini : '';
        $title.= (!empty($dados->data_fim)) ? ' até ' . $dados->data_fim : '';
        
        // cria um painél para conter o formulário
        $panel = new Panel($title);
        $panel->add($content);
        
        parent::add($panel);
    }
}
