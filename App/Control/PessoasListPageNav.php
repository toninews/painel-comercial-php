<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Combo;
use Nexa\Widgets\Container\Panel;
use Nexa\Widgets\Container\VBox;
use Nexa\Widgets\Datagrid\Datagrid;
use Nexa\Widgets\Datagrid\DatagridColumn;
use Nexa\Widgets\Datagrid\PageNavigation;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Dialog\Question;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Wrapper\DatagridWrapper;

/**
 * Listagem de Pessoas
 */
class PessoasListPageNav extends Page
{
    private $form;     // formulário de buscas
    private $datagrid; // listagem
    private $loaded;
    private $pagenav;
    private $totalRecords = 0;
    private $pageSize = 10;

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper(new Form('form_busca_pessoas'));
        $this->form->setTitle('Pessoas com paginação');
        
        $nome = new Entry('nome');
        $nome->placeholder = 'Buscar por nome com navegação paginada';
        
        $this->form->addField('Nome', $nome, '100%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Novo', new Action(array(new PessoasForm, 'onEdit')));
        
        $this->datagrid = new DatagridWrapper(new Datagrid);
        $this->datagrid->setEmptyMessage('Nenhuma pessoa encontrada nesta página.');

        $codigo   = new DatagridColumn('id',         'Código', 'center', '10%');
        $nome     = new DatagridColumn('nome',       'Nome',   'left', '35%');
        $endereco = new DatagridColumn('endereco',   'Endereco', 'left', '35%');
        $cidade   = new DatagridColumn('nome_cidade','Cidade', 'left', '20%');

        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($endereco);
        $this->datagrid->addColumn($cidade);

        $this->datagrid->addAction('Editar', new Action([new PessoasForm, 'onEdit']), 'id', 'fa fa-pencil');
        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash');

        $this->pagenav = new PageNavigation;
        $this->pagenav->setAction(new Action(array($this, 'onReload')));
        $this->pagenav->setPageSize($this->pageSize);
        
        $box = new VBox;
        $box->style = 'display:block';
        $box->add(new Fragment(function () {
            return $this->renderIntro();
        }));
        $box->add($this->form);
        $box->add($this->datagrid);
        $box->add($this->pagenav);
        
        parent::add($box);
    }

    /**
     * Carrega a Datagrid com os objetos do banco de dados
     */
    public function onReload($param)
    {
        $dados = $this->form->getData();
        $offset = isset($param['offset']) ? (int) $param['offset'] : 0;
        $currentPage = isset($param['page']) ? (int) $param['page'] : 1;
        $result = PessoaListingService::search($dados->nome ?: null, $this->pageSize, $offset);
        $pessoas = $result['items'];
        $this->totalRecords = (int) $result['count'];
        
        $this->datagrid->clear();
        if ($pessoas)
        {
            foreach ($pessoas as $pessoa)
            {
                // adiciona o objeto na Datagrid
                $this->datagrid->addItem($pessoa);
            }
        }

        $this->pagenav->setTotalRecords($this->totalRecords);
        $this->pagenav->setCurrentPage($currentPage);
        $this->loaded = true;
    }

    private function renderIntro()
    {
        $count = (int) $this->totalRecords;

        return "
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Navegação paginada</span>
                    <h2>Consulta de pessoas com paginação clara.</h2>
                    <p>Esta variante da listagem mostra uma base maior sem carregar todos os registros de uma vez, deixando a leitura mais leve e previsível.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Total encontrado</span>
                    <strong>{$count}</strong>
                    <small>Registros retornados pelo filtro atual, distribuídos em páginas de {$this->pageSize} itens.</small>
                </article>
            </section>
        ";
    }

    /**
     * Pergunta sobre a exclusão de registro
     */
    public function onDelete($param)
    {
        $key = $param['key'];
        $action1 = new Action(array($this, 'onDeleteConfirmed'));
        $action1->setParameter('key', $key);
        
        new Question('Deseja realmente excluir o registro?', $action1);
    }

    /**
     * Exclui um registro
     */
    public function onDeleteConfirmed($param)
    {
        try
        {
            PessoaApplicationService::delete($param['key']);
            $this->onReload($param);
            new Message('info', 'Registro excluído com sucesso');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    /**
     * Exibe a página
     */
    public function show()
    {
         // se a listagem ainda não foi carregada
         if (!$this->loaded)
         {
	        $this->onReload( $_GET );
         }
         parent::show();
    }
}
