<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Combo;
use Nexa\Widgets\Form\CheckGroup;
use Nexa\Widgets\Container\Panel;
use Nexa\Widgets\Wrapper\FormWrapper;

/**
 * Formulário de pessoas
 */
class PessoasForm extends Page
{
    private $form;

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_pessoas'));
        $this->form->setTitle('Pessoa');
        
        // cria os campos do formulário
        $codigo    = new Entry('id');
        $nome      = new Entry('nome');
        $endereco  = new Entry('endereco');
        $bairro    = new Entry('bairro');
        $telefone  = new Entry('telefone');
        $email     = new Entry('email');
        $cidade    = new Combo('id_cidade');
        $grupo     = new CheckGroup('ids_grupos');
        $grupo->setLayout('horizontal');

        $options = PessoaFormOptionsService::load();
        $cidade->addItems($options['cidades']);
        $grupo->addItems($options['grupos']);

        $nome->placeholder = 'Nome completo';
        $endereco->placeholder = 'Rua, número e complemento';
        $bairro->placeholder = 'Bairro';
        $telefone->placeholder = '(00) 0000-0000';
        $email->placeholder = 'email@dominio.com';
        
        $this->form->addField('Código', $codigo, '30%');
        $this->form->addField('Nome', $nome, '70%');
        $this->form->addField('Endereço', $endereco, '70%');
        $this->form->addField('Bairro', $bairro, '70%');
        $this->form->addField('Telefone', $telefone, '70%');
        $this->form->addField('Email', $email, '70%');
        $this->form->addField('Cidade', $cidade, '70%');
        $this->form->addField('Grupo', $grupo, '70%');
        
        // define alguns atributos para os campos do formulário
        $codigo->setEditable(FALSE);
        
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        
        // adiciona o formulário na página
        parent::add($this->renderIntro());
        parent::add($this->form);
    }

    /**
     * Salva os dados do formulário
     */
    public function onSave()
    {
        try
        {
            $dados = $this->form->getData();
            $this->form->setData($dados);
            PessoaApplicationService::save($dados);
            new Message('info', 'Dados armazenados com sucesso');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }
    
    /**
     * Carrega registro para edição
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['id']))
            {
                $pessoa = PessoaApplicationService::loadForEdit($param['id']);
                $this->form->setData($pessoa);
            }
        }
        catch (Exception $e)		    // em caso de exceção
        {
            // exibe a mensagem gerada pela exceção
            new Message('error', $e->getMessage());
        }
    }

    private function renderIntro()
    {
        return '
            <section class="crud-hero">
                <div class="crud-hero__content">
                    <span class="crud-hero__eyebrow">Cadastro principal</span>
                    <h2>Pessoas e grupos em um fluxo mais claro.</h2>
                    <p>Use este formulário para manter a base de clientes, fornecedores, revendedores e colaboradores com os grupos vinculados.</p>
                </div>
            </section>
        ';
    }
}
