<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Combo;
use Nexa\Widgets\Form\RadioGroup;

use Nexa\Widgets\Wrapper\DatagridWrapper;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;

/**
 * Cadastro de Produtos
 */
class ProdutosForm extends Page
{
    private $form; // formulário
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_produtos'));
        $this->form->setTitle('Produto');
        
        // cria os campos do formulário
        $codigo      = new Entry('id');
        $descricao   = new Entry('descricao');
        $estoque     = new Entry('estoque');
        $preco_custo = new Entry('preco_custo');
        $preco_venda = new Entry('preco_venda');
        $fabricante  = new Combo('id_fabricante');
        $tipo        = new RadioGroup('id_tipo');
        $unidade     = new Combo('id_unidade');

        $options = ProdutoFormOptionsService::load();
        $fabricante->addItems($options['fabricantes']);
        $tipo->addItems($options['tipos']);
        $unidade->addItems($options['unidades']);

        $descricao->placeholder = 'Nome comercial do produto';
        $estoque->placeholder = '0';
        $preco_custo->placeholder = '0,00';
        $preco_venda->placeholder = '0,00';
        
        // define alguns atributos para os campos do formulário
        $codigo->setEditable(FALSE);
        
        $this->form->addField('Código',    $codigo, '30%');
        $this->form->addField('Descrição', $descricao, '70%');
        $this->form->addField('Estoque',   $estoque, '70%');
        $this->form->addField('Preço custo',   $preco_custo, '70%');
        $this->form->addField('Preço venda',   $preco_venda, '70%');
        $this->form->addField('Fabricante',   $fabricante, '70%');
        $this->form->addField('Tipo',   $tipo, '70%');
        $this->form->addField('Unidade',   $unidade, '70%');
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        
        // adiciona o formulário na página
        parent::add($this->renderIntro());
        parent::add($this->form);
    }

    public function onSave()
    {
        try
        {
            $dados = $this->form->getData();
            $produto = ProdutoApplicationService::save($dados);
            $dados->id = $produto->id;
            $this->form->setData($dados);
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
            if (isset($param['id']))
            {
                $produto = ProdutoApplicationService::loadForEdit($param['id']);
                $this->form->setData($produto);
            }
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    private function renderIntro()
    {
        return '
            <section class="crud-hero">
                <div class="crud-hero__content">
                    <span class="crud-hero__eyebrow">Catálogo</span>
                    <h2>Produtos com precificação e classificação organizadas.</h2>
                    <p>Centralize estoque, fabricante, tipo e unidade em um cadastro mais pronto para portfólio e demonstração comercial.</p>
                </div>
            </section>
        ';
    }
}
