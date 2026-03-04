<?php
use Nexa\Control\Page;
use Nexa\Control\Action;
use Nexa\Widgets\Form\Form;
use Nexa\Widgets\Form\Entry;
use Nexa\Widgets\Form\Password;
use Nexa\Widgets\Wrapper\FormWrapper;
use Nexa\Widgets\Container\Panel;
/**
 * Formulário de Login
 */
class LoginForm extends Page
{
    private $form; // formulário
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_login'));
        $this->form->setTitle('Login');
        
        $login      = new Entry('login');
        $password   = new Password('password');
        
        $login->placeholder    = 'admin';
        $password->placeholder = 'admin';
        
        $this->form->addField('Login',    $login,    200);
        $this->form->addField('Senha',    $password, 200);
        $this->form->addAction('Login', new Action(array($this, 'onLogin')));
        
        // adiciona o formulário na página
        parent::add($this->form);
    }
    
    /**
     * Login
     */
    public function onLogin($param)
    {
        $data = $this->form->getData();
        if (AuthService::attempt($data->login, $data->password)) {
            $target = NavigationService::getDefaultPrivatePage();
            if (!headers_sent()) {
                header("Location: index-login.php?class={$target}");
                exit;
            }

            echo "<script language='JavaScript'> window.location = 'index-login.php?class={$target}'; </script>";
            return;
        }

        throw new Exception('Usuário ou senha inválidos.');
    }
    
    /**
     * Logout
     */
    public function onLogout($param)
    {
        AuthService::logout();
        if (!headers_sent()) {
            header('Location: index-login.php');
            exit;
        }

        echo "<script language='JavaScript'> window.location = 'index-login.php'; </script>";
    }
}
