<?php
require_once 'bootstrap.php';

use Nexa\Control\RouteGuard;

$content = '';
$class   = '';

if (isset($_GET['class']))
{
    $requestedClass = $_GET['class'];

    if (RouteGuard::isAllowedPageClass($requestedClass))
    {
        $class = $requestedClass;

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
    else
    {
        http_response_code(404);
        $content = 'Pagina nao encontrada.';
    }
}

echo TemplateService::render('App/Templates/template.html', [
    'content' => $content,
    'class' => $class,
    'app_name' => NavigationService::getAppName(),
    'app_tagline' => NavigationService::getTagline(),
    'menu' => NavigationService::renderMenu($class),
    'shortcuts' => NavigationService::renderShortcuts(),
    'default_page' => NavigationService::getDefaultPrivatePage(),
]);
