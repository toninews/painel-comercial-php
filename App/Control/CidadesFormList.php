<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Combo;
use Nexa\Widgets\Container\VBox;
use Nexa\Widgets\Datagrid\Datagrid;
use Nexa\Widgets\Datagrid\DatagridColumn;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Dialog\Question;

use Nexa\Widgets\Wrapper\DatagridWrapper;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

/**
 * Cadastro de cidades
 */
class CidadesFormList extends Page
{
    private $form;
    private $datagrid;
    private $loaded;
    private $totalRecords = 0;
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_cidades'));
        $this->form->setTitle('Cidades');
        
        // cria os campos do formulário
        $codigo    = new Entry('id');
        $descricao = new Entry('nome');
        $estado    = new Combo('id_estado');
        
        $codigo->setEditable(FALSE);
        $descricao->placeholder = 'Informe o nome da cidade';
        $estado->addItems(CidadeFormOptionsService::loadEstados());
        
        $this->form->addField('Código', $codigo, '30%');
        $this->form->addField('Descrição', $descricao, '70%');
        $this->form->addField('Estado', $estado, '70%');
        
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));
        
        // instancia a Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);
        $this->datagrid->setEmptyMessage('Nenhuma cidade cadastrada no momento.');

        // instancia as colunas da Datagrid
        $codigo   = new DatagridColumn('id',     'Código', 'center', '10%');
        $nome     = new DatagridColumn('nome',   'Nome',   'left', '50%');
        $estado   = new DatagridColumn('nome_estado', 'Estado', 'left', '40%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($estado);

        $this->datagrid->addAction('Editar', new Action([$this, 'onEdit']), 'id', 'fa fa-pencil');
        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash');
        
        // monta a página através de uma tabela
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
     * Salva os dados
     */
    public function onSave()
    {
        try
        {
            $dados = $this->form->getData();
            $cidade = CidadeApplicationService::save($dados);
            $dados->id = $cidade->id;
            $this->form->setData($dados);
            $this->onReload();
            new Message('info', 'Dados armazenados com sucesso');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    public function onEdit($param)
    {
        try
        {
            $id = isset($param['id']) ? $param['id'] : (isset($param['key']) ? $param['key'] : null);
            if ($id)
            {
                $cidade = CidadeApplicationService::loadForEdit($id);
                $this->form->setData($cidade);
            }
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
            CidadeApplicationService::delete($param['id']);
            $this->onReload();
            new Message('info', 'Registro excluído com sucesso');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }
    
    /**
     * Carrega os dados
     */
    public function onReload()
    {
        try
        {
            $objects = CidadeListingService::load();
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

    private function renderIntro()
    {
        $count = (int) $this->totalRecords;

        return "
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Cadastro de localização</span>
                    <h2>Organize cidades com leitura mais objetiva.</h2>
                    <p>Use esta tela para manter a base geográfica do sistema de forma mais clara, com estado associado e visual menos datado.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Cidades listadas</span>
                    <strong>{$count}</strong>
                    <small>Total exibido atualmente na grade de cidades.</small>
                </article>
            </section>
        ";
    }

    /**
     * exibe a página
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
