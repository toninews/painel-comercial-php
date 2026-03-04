<?php
use Nexa\Control\Page;
use Nexa\Session\Session;
use Nexa\Widgets\Container\Panel;

class SessionTest extends Page
{
    public function __construct()
    {
        parent::__construct();
        
        new Session;
        
        Session::setValue('teste1', 456);
        $value = Session::getValue('teste1');

        $panel = new Panel('Diagnóstico de sessão');
        $panel->add("
            <section class=\"crud-hero\">
                <div class=\"crud-hero__content\">
                    <span class=\"crud-hero__eyebrow\">Sessão</span>
                    <h2>Verificação simples de persistência.</h2>
                    <p>Uso mínimo para confirmar escrita e leitura da sessão do projeto sem saída crua na página.</p>
                </div>
            </section>
            <section class=\"crud-summary crud-summary--single\">
                <article class=\"crud-summary__card\">
                    <span>Valor armazenado</span>
                    <strong>{$value}</strong>
                    <small>Resultado da chave de teste gravada e lida na mesma requisição.</small>
                </article>
            </section>
        ");

        parent::add($panel);
    }
}
