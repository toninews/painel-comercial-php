<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Container\VBox;
use Nexa\Widgets\Datagrid\Datagrid;
use Nexa\Widgets\Datagrid\DatagridColumn;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Dialog\Message;

use Nexa\Widgets\Wrapper\DatagridWrapper;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

/**
 * Página de vendas
 */
class VendasForm extends Page
{
    private $form;
    private $datagrid;
    private $loaded;

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        VendaDraftService::initialize();

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_vendas'));
        $this->form->setTitle('Venda');
        
        // cria os campos do formulário
        $codigo      = new Entry('id_produto');
        $quantidade  = new Entry('quantidade');

        $codigo->placeholder = 'Código do produto';
        $quantidade->placeholder = 'Quantidade';
        
        $this->form->addField('Código', $codigo, '50%');
        $this->form->addField('Quantidade', $quantidade, '50%');
        $this->form->addAction('Adicionar', new Action(array($this, 'onAdiciona')));
        $this->form->addAction('Finalizar venda', new Action(array(new ConcluiVendaForm, 'onLoad')));
        
        // instancia objeto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $codigo    = new DatagridColumn('id_produto', 'Código', 'center', '20%');
        $descricao = new DatagridColumn('descricao',   'Descrição','left', '40%');
        $quantidade= new DatagridColumn('quantidade',  'Qtde',      'right', '20%');
        $preco     = new DatagridColumn('preco',       'Preço',    'right', '20%');

        // define um transformador para a coluna preço
        $preco->setTransformer(array($this, 'formata_money'));

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($descricao);
        $this->datagrid->addColumn($quantidade);
        $this->datagrid->addColumn($preco);

        $this->datagrid->addAction( 'Excluir',  new Action([$this, 'onDelete']), 'id_produto', 'fa fa-trash fa-lg red');
        
        // monta a página através de uma caixa
        $box = new VBox;
        $box->style = 'display:block';
        $box->add(new Fragment(function () {
            return $this->renderIntro();
        }));
        $box->add($this->form);
        $box->add(new Fragment(function () {
            return $this->renderSummary();
        }));
        $box->add($this->datagrid);
        
        parent::add($box);
    }
    
    /**
     * Adiciona item
     */
    public function onAdiciona()
    {
        try
        {
            $item = $this->form->getData();

            if (empty($item->id_produto))
            {
                throw new Exception('Informe o código do produto.');
            }

            if (empty($item->quantidade) || $item->quantidade <= 0)
            {
                throw new Exception('Informe uma quantidade válida.');
            }

            VendaDraftService::addProductById($item->id_produto, $item->quantidade);
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
        
        // recarrega a listagem
        $this->onReload();
    }

    /**
     * Exclui item
     */
    public function onDelete($param)
    {
        VendaSessionService::remove($param['id_produto']);

        // recarrega a listagem
        $this->onReload();
    }

    /**
     * Carrega datagrid com objetos
     */
    public function onReload()
    {
        // obtém a variável de seção $list
        $list = VendaSessionService::all();

        // limpa a datagrid
        $this->datagrid->clear();
        if ($list)
        {
            foreach ($list as $item)
            {
                // adiciona cada objeto $item na datagrid
                $this->datagrid->addItem($item);
            }
        }
        $this->loaded = true;
    }
    
    /**
     * Formata valor monetário
     */
    public function formata_money($valor)
    {
        return number_format($valor, 2, ',', '.');
    }

    private function renderIntro()
    {
        return '
            <section class="crud-hero">
                <div class="crud-hero__content">
                    <span class="crud-hero__eyebrow">Fluxo comercial</span>
                    <h2>Monte a venda com menos atrito.</h2>
                    <p>Adicione itens pelo código do produto e acompanhe o total antes de concluir o pedido e gerar as parcelas.</p>
                </div>
            </section>
        ';
    }

    private function renderSummary()
    {
        $summary = VendaDraftService::summary();
        $count = $summary['count'];
        $total = number_format($summary['total'], 2, ',', '.');

        return "
            <section class=\"crud-summary\">
                <article class=\"crud-summary__card\">
                    <span>Itens no pedido</span>
                    <strong>{$count}</strong>
                    <small>Quantidade de produtos adicionados no carrinho da venda.</small>
                </article>
                <article class=\"crud-summary__card\">
                    <span>Total parcial</span>
                    <strong>R$ {$total}</strong>
                    <small>Atualizado automaticamente conforme os itens são inseridos ou removidos.</small>
                </article>
            </section>
        ";
    }
    
    /**
     * Exibe a página
     */
    public function show()
    {
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
}
