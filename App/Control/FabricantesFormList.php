<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Container\VBox;
use Nexa\Widgets\Base\Fragment;
use Nexa\Widgets\Datagrid\Datagrid;
use Nexa\Widgets\Datagrid\DatagridColumn;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Dialog\Question;

use Nexa\Widgets\Wrapper\DatagridWrapper;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

/*
 * classe FabricantesFormList
 * Cadastro de Fabricantes
 * Contém o formuláro e a listagem
 */
class FabricantesFormList extends Page
{
    private $form;      // formulário de cadastro
    private $datagrid;  // listagem
    private $loaded;
    private $totalRecords = 0;
    
    
    /*
     * método construtor
     * Cria a página, o formulário e a listagem
     */
    public function __construct()
    {
        parent::__construct();
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_fabricantes'));
        $this->form->setTitle('Fabricantes');
        
        // cria os campos do formulário
        $codigo = new Entry('id');
        $nome   = new Entry('nome');
        $site   = new Entry('site');
        $codigo->setEditable(FALSE);
        $nome->placeholder = 'Informe o nome do fabricante';
        $site->placeholder = 'https://site-do-fabricante.com';
        
        $this->form->addField('Código', $codigo, '30%');
        $this->form->addField('Nome',   $nome, '70%');
        $this->form->addField('Site',   $site, '70%');
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));
        
        // instancia objeto DataGrid
        $this->datagrid = new DatagridWrapper(new DataGrid);
        $this->datagrid->setEmptyMessage('Nenhum fabricante cadastrado no momento.');
        
        // instancia as colunas da DataGrid
        $codigo   = new DataGridColumn('id',       'Código',  'center',  '10%');
        $nome     = new DataGridColumn('nome',     'Nome',    'left',  '60%');
        $site     = new DataGridColumn('site',     'Site',    'left',  '30%');
        
        // adiciona as colunas à DataGrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($site);
        
        $this->datagrid->addAction('Editar', new Action([$this, 'onEdit']), 'id', 'fa fa-pencil');
        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash');
        
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
     * Salva os dados
     */
    public function onSave()
    {
        try
        {
            $dados = $this->form->getData();
            $fabricante = FabricanteApplicationService::save($dados);
            $dados->id = $fabricante->id;
            $this->form->setData($dados);
            $this->onReload();
            new Message('info', 'Dados armazenados com sucesso');
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
            $objects = FabricanteListingService::load();
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
                    <span class=\"crud-hero__eyebrow\">Catálogo de origem</span>
                    <h2>Fabricantes com visual mais consistente.</h2>
                    <p>Mantenha a origem dos produtos com uma tela mais atual, sem a apresentação crua e engessada do projeto original.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Fabricantes listados</span>
                    <strong>{$count}</strong>
                    <small>Total atualmente exibido na grade de fabricantes.</small>
                </article>
            </section>
        ";
    }
    
    /**
     * Carrega registro para edição
     */
    public function onEdit($param)
    {
        try
        {
            $id = isset($param['id']) ? $param['id'] : (isset($param['key']) ? $param['key'] : null);
            if ($id)
            {
                $fabricante = FabricanteApplicationService::loadForEdit($id);
                $this->form->setData($fabricante);
                $this->onReload();
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
            FabricanteApplicationService::delete($param['id']);
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
