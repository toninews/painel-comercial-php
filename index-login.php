<?php
require_once 'bootstrap.php';

use Nexa\Control\RouteGuard;

function buildDemoAccountBlock()
{
    if (!AuthService::hasPublicDemoAccount())
    {
        return '';
    }

    $login = htmlspecialchars(AuthService::getDemoDisplayLogin(), ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars(AuthService::getDemoDisplayPassword(), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<aside class="demo-access-card">
    <span class="demo-access-card__eyebrow">Usar conta demo</span>
    <h2>Explore o sistema sem criar usuario.</h2>
    <p>Login: <strong>{$login}</strong> | Senha: <strong>{$password}</strong></p>
    <div class="demo-access-card__actions">
        <button type="button" class="btn btn-default demo-access-card__button" data-demo-login="{$login}" data-demo-password="{$password}">
            Usar conta demo
        </button>
    </div>
</aside>
HTML;
}

$content = '';
AuthService::boot();
$isLogged = AuthService::isLogged();
$class = $isLogged ? NavigationService::getDefaultPrivatePage() : 'LoginForm';

if (isset($_GET['class']) && $isLogged)
{
    $requestedClass = $_GET['class'];

    if (RouteGuard::isAllowedPageClass($requestedClass)) {
        $class = $requestedClass;
    }
    else {
        http_response_code(404);
        $content = 'Pagina nao encontrada.';
    }
}

if ($class && RouteGuard::isAllowedPageClass($class))
{
    try
    {
        $pagina = new $class;
        ob_start();
        $pagina->show();
        $content = ob_get_contents();
        ob_end_clean();
    }
    catch (Exception $e)
    {
        error_log($e);
        $content = 'Ocorreu um erro ao carregar a pagina.';
    }
}

$isLogged = AuthService::isLogged();

if ($isLogged && $class === 'LoginForm') {
    $class = NavigationService::getDefaultPrivatePage();
}

$template = $isLogged ? 'App/Templates/template.html' : 'App/Templates/login.html';

echo TemplateService::render($template, [
    'content' => $content,
    'demo_block' => $isLogged ? '' : buildDemoAccountBlock(),
    'class' => $class,
    'app_name' => NavigationService::getAppName(),
    'app_tagline' => NavigationService::getTagline(),
    'menu' => $isLogged ? NavigationService::renderMenu($class) : '',
    'shortcuts' => $isLogged ? NavigationService::renderShortcuts() : '',
    'default_page' => NavigationService::getDefaultPrivatePage(),
]);
