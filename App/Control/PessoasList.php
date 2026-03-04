<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Container\VBox;
use Nexa\Widgets\Datagrid\Datagrid;
use Nexa\Widgets\Datagrid\DatagridColumn;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Dialog\Question;
use Nexa\Widgets\Container\Panel;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Wrapper\DatagridWrapper;

/**
 * Listagem de Pessoas
 */
class PessoasList extends Page
{
    private $form;     // formulário de buscas
    private $datagrid; // listagem
    private $loaded;
    private $totalRecords = 0;

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();
        
        // instancia um formulário de buscas
        $this->form = new FormWrapper(new Form('form_busca_pessoas'));
        $this->form->setTitle('Pessoas');
        
        $nome = new Entry('nome');
        $nome->placeholder = 'Buscar por nome, cliente ou contato';
        $this->form->addField('Nome', $nome, '100%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Novo', new Action(array(new PessoasForm, 'onEdit')));
        
        // instancia objeto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);
        $this->datagrid->setEmptyMessage('Nenhuma pessoa encontrada para este filtro.');

        // instancia as colunas da Datagrid
        $codigo   = new DatagridColumn('id',         'Código', 'center', '10%');
        $nome     = new DatagridColumn('nome',       'Nome',    'left', '40%');
        $endereco = new DatagridColumn('endereco',   'Endereço','left', '30%');
        $cidade   = new DatagridColumn('nome_cidade','Cidade', 'left', '20%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($endereco);
        $this->datagrid->addColumn($cidade);

        $this->datagrid->addAction( 'Editar',  new Action([new PessoasForm, 'onEdit']), 'id', 'fa fa-pencil');
        $this->datagrid->addAction( 'Excluir',  new Action([$this, 'onDelete']),         'id', 'fa fa-trash');
        
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

    /**
     * Carrega a Datagrid com os objetos do banco de dados
     */
    public function onReload()
    {
        // obtém os dados do formulário de buscas
        $dados = $this->form->getData();
        $result = PessoaListingService::search($dados->nome ?: null);
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

        $this->loaded = true;
    }

    private function renderIntro()
    {
        $count = (int) $this->totalRecords;

        return "
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Base de relacionamento</span>
                    <h2>Gerencie contatos com leitura mais clara.</h2>
                    <p>Esta listagem concentra clientes, fornecedores e outros perfis cadastrados, facilitando busca, edição e manutenção do cadastro.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Registros listados</span>
                    <strong>{$count}</strong>
                    <small>Total retornado pelo filtro atual da tela de pessoas.</small>
                </article>
            </section>
        ";
    }

    /**
     * Pergunta sobre a exclusão de registro
     */
    public function onDelete($param)
    {
        $id = $param['id']; // obtém o parâmetro $id
        $action1 = new Action(array($this, 'onDeleteConfirmed'));
        $action1->setParameter('id', $id);
        
        new Question('Deseja realmente excluir o registro?', $action1);
    }

    /**
     * Exclui um registro
     */
    public function onDeleteConfirmed($param)
    {
        try
        {
            PessoaApplicationService::delete($param['id']);
            $this->onReload();
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
	        $this->onReload();
         }
         parent::show();
    }
}
