<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Date;
use Nexa\Widgets\Dialog\Message;

use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Relatório de contas
 */
class ContasReport extends Page
{
    private $form;   // formulário de entrada

    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_relat_contas'));
        $this->form->setTitle('Relatório de contas');
        
        // cria os campos do formulário
        $data_ini = new Date('data_ini');
        $data_fim = new Date('data_fim');
        
        $this->form->addField('Vencimento Inicial', $data_ini, '50%');
        $this->form->addField('Vencimento Final', $data_fim, '50%');
        $this->form->addAction('Gerar', new Action(array($this, 'onGera')));
        $this->form->addAction('PDF', new Action(array($this, 'onGeraPDF')));
        
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
        
        try
        {
            $replaces = array_merge($replaces, ContasReportService::build($data_ini, $data_fim));
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
        $content = $twig->render('contas_report.html', $replaces);
        
        $title = 'Contas';
        $title.= (!empty($dados->data_ini)) ? ' de '  . $dados->data_ini : '';
        $title.= (!empty($dados->data_fim)) ? ' até ' . $dados->data_fim : '';
        
        // cria um painél para conter o formulário
        $panel = new Panel($title);
        $panel->add($content);
        
        parent::add($panel);
        
        return $content;
    }
    
    /**
     * Gera o relatório em PDF, baseado nos parâmetros do formulário
     */
    public function onGeraPDF($param)
    {
        // gera o relatório em HTML primeiro
        $html = $this->onGera($param);
        
        $options = new Options();
        $options->set('dpi', '128');

        // DomPDF converte o HTML para PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Escreve o arquivo e abre em tela
        $filename = 'tmp/contas.pdf';
        if (is_writable('tmp'))
        {
            file_put_contents($filename, $dompdf->output());
            echo "<script>window.open('{$filename}');</script>";
        }
        else
        {
            new Message('error', 'Permissão negada em: ' . $filename);
        }
    }
}
