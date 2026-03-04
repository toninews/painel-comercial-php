<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Combo;
use Nexa\Widgets\Form\Text;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;
use Nexa\Session\Session;

/**
 * Formulário de conclusão de venda
 */
class ConcluiVendaForm extends Page
{
    private $form;
    
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();
        
        VendaDraftService::initialize();
        
        $this->form = new FormWrapper(new Form('form_conclui_venda'));
        $this->form->setTitle('Finalizar venda');
        
        // cria os campos do formulário
        $cliente      = new Combo('id_cliente');
        $valor_venda  = new Entry('valor_venda');
        $desconto     = new Entry('desconto');
        $acrescimos   = new Entry('acrescimos');
        $valor_final  = new Entry('valor_final');
        $parcelas     = new Combo('parcelas');
        $obs          = new Text('obs');

        $cliente->addItems(ClienteOptionsService::load());
        $cliente->setProperty('required', 'required');
        $cliente->setProperty('title', 'Selecione o cliente');
        $desconto->placeholder = '0,00';
        $acrescimos->placeholder = '0,00';
        $obs->placeholder = 'Observações da venda';
        
        $parcelas->addItems(array(1=>'Uma', 2=>'Duas', 3=>'Três'));
        $parcelas->setValue(1);

        // define uma ação de cálculo Javascript
        $desconto->onBlur = "$('[name=valor_final]').val( Number($('[name=valor_venda]').val()) + Number($('[name=acrescimos]').val()) - Number($('[name=desconto]').val()) );";
        $acrescimos->onBlur = $desconto->onBlur;
        
        $valor_venda->setEditable(FALSE);
        $valor_final->setEditable(FALSE);
        
        $this->form->addField('Cliente', $cliente,   '50%');
        $this->form->addField('Valor', $valor_venda, '50%');
        $this->form->addField('Desconto', $desconto, '50%');
        $this->form->addField('Acréscimos', $acrescimos, '50%');
        $this->form->addField('Final', $valor_final, '50%');
        $this->form->addField('Parcelas', $parcelas, '50%');
        $this->form->addField('Obs', $obs, '50%');
        $this->form->addAction('Finalizar venda', new Action(array($this, 'onGravaVenda')));
        $this->form->addAction('Voltar', new Action(array($this, 'onVoltar')));
        
        parent::add(new Fragment(function () {
            return $this->renderIntro();
        }));
        parent::add($this->form);
    }
    
    /**
     * Carrega formulário de conclusão
     */
    public function onLoad($param)
    {
        try
        {
            $data = VendaCheckoutService::defaultFormData();
            $itens = VendaSessionService::all();

            if (!$itens)
            {
                new Message('error', 'Adicione pelo menos um item antes de concluir a venda.');
                echo "<script>window.location='index-login.php?class=VendasForm';</script>";
                return;
            }

            $this->form->setData($data);
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            echo "<script>window.location='index-login.php?class=VendasForm';</script>";
        }
    }
    
    /**
     * Grava venda
     */
    public function onGravaVenda()
    {
        try 
        {
            VendaApplicationService::finalize($this->form->getData());
            $this->redirectTo('index-login.php?class=VendasReport&method=onGera&sale_saved=1');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    public function onVoltar()
    {
        $this->redirectTo('index-login.php?class=VendasForm');
    }

    private function renderIntro()
    {
        $summary = VendaDraftService::summary();
        $itemsCount = $summary['count'];
        $total = number_format($summary['total'], 2, ',', '.');

        return "
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Fechamento</span>
                    <h2>Confirme cliente, condições e total da venda.</h2>
                    <p>Agora o cliente pode ser escolhido diretamente no formulário de conclusão, evitando erro manual no código e deixando o fluxo mais natural.</p>
                </div>
            </section>
            <section class=\"crud-summary\">
                <article class=\"crud-summary__card\">
                    <span>Itens separados</span>
                    <strong>{$itemsCount}</strong>
                    <small>Quantidade atual de itens que serão convertidos em venda.</small>
                </article>
                <article class=\"crud-summary__card\">
                    <span>Total projetado</span>
                    <strong>R$ {$total}</strong>
                    <small>Total bruto carregado automaticamente antes de aplicar desconto ou acréscimo.</small>
                </article>
            </section>
        ";
    }

    private function redirectTo($url)
    {
        if (!headers_sent())
        {
            header("Location: {$url}");
            exit;
        }

        echo "<script>window.location='{$url}';</script>";
    }
}
