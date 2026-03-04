<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Container\VBox;
use Nexa\Widgets\Datagrid\Datagrid;
use Nexa\Widgets\Datagrid\DatagridColumn;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Dialog\Question;
use Nexa\Widgets\Wrapper\DatagridWrapper;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

/**
 * Página de produtos
 */
class ProdutosList extends Page
{
    private $form;
    private $datagrid;
    private $loaded;
    private $filters;
    private $totalRecords = 0;
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_busca_produtos'));
        $this->form->setTitle('Produtos');
        
        // cria os campos do formulário
        $descricao = new Entry('descricao');
        $descricao->placeholder = 'Buscar por descrição do produto';
        
        $this->form->addField('Descrição',   $descricao, '100%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Cadastrar', new Action(array(new ProdutosForm, 'onEdit')));
        
        // instancia objeto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);
        $this->datagrid->setEmptyMessage('Nenhum produto encontrado para este filtro.');
        
        // instancia as colunas da Datagrid
        $codigo   = new DatagridColumn('id',             'Código',    'center',  '10%');
        $descricao= new DatagridColumn('descricao',      'Descrição', 'left',   '30%');
        $fabrica  = new DatagridColumn('nome_fabricante','Fabricante','left',   '30%');
        $estoque  = new DatagridColumn('estoque',        'Estoq.',    'right',  '15%');
        $preco    = new DatagridColumn('preco_venda',    'Venda',     'right',  '15%');
        
        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($descricao);
        $this->datagrid->addColumn($fabrica);
        $this->datagrid->addColumn($estoque);
        $this->datagrid->addColumn($preco);
        
        $this->datagrid->addAction( 'Editar',  new Action([new ProdutosForm, 'onEdit']), 'id', 'fa fa-pencil');
        $this->datagrid->addAction( 'Excluir', new Action([$this, 'onDelete']),          'id', 'fa fa-trash');
        
        // monta a página através de uma caixa
        $box = new VBox;
        $box->style = 'display:block';
        $box->add(new Fragment(function () {
            return $this->renderIntro();
        }));
        $box->add($this->form);
        $box->add($this->datagrid);
        
        parent::add($box);
    }
    
    public function onReload()
    {
        try
        {
            $this->filters = [];
            $dados = $this->form->getData();

            if ($dados->descricao)
            {
                $this->filters[] = ['descricao', 'like', "%{$dados->descricao}%", 'and'];
            }

            $objects = ProdutoListingService::load($this->filters);

            $this->datagrid->clear();
            foreach ($objects as $object)
            {
                $this->datagrid->addItem($object);
            }

            $this->totalRecords = count($objects);
            $this->loaded = true;
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    public function onDelete($param)
    {
        $id = isset($param['id']) ? $param['id'] : $param['key'];
        $action = new Action([$this, 'onDeleteConfirmed']);
        $action->setParameter('id', $id);

        new Question('Deseja realmente excluir o registro?', $action);
    }

    public function onDeleteConfirmed($param)
    {
        try
        {
            ProdutoApplicationService::delete($param['id']);
            $this->onReload();
            new Message('info', 'Registro excluído com sucesso');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    private function renderIntro()
    {
        $count = (int) $this->totalRecords;

        return "
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Catálogo comercial</span>
                    <h2>Produtos com consulta mais objetiva.</h2>
                    <p>Use a busca para navegar pelo catálogo, revisar fabricante, estoque e preço de venda sem depender do layout antigo do curso.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Itens listados</span>
                    <strong>{$count}</strong>
                    <small>Total retornado pela pesquisa atual da tela de produtos.</small>
                </article>
            </section>
        ";
    }
    
    /**
     * Exibe a página
     */
    public function show()
    {
         // se a listagem ainda não foi carregada
         if (!$this->loaded)
         {
	        $this->onReload();
         }
         parent::show();
    }
}
