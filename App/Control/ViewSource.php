<?php
use Nexa\Control\Page;

use Nexa\Widgets\Container\Panel;

/**
 * Exibe código-fonte
 */
class ViewSource extends Page
{
    private $form; // formulário
    
    public function onView($param)
    {
        throw new Exception('Visualização de código desabilitada neste ambiente.');
    }
}
