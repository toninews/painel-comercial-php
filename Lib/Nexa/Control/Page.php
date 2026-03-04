<?php
namespace Nexa\Control;

use Nexa\Widgets\Base\Element;

/**
 * Page controller
 */
class Page extends Element
{
    /**
     * Método construtor
     */
    public function __construct()
    {
        parent::__construct('div');
    }
    
    /**
     * Executa determinado método de acordo com os parâmetros recebidos
     */
    public function show()
    {
        if ($_GET)
        {
            $class  = isset($_GET['class'])  ? $_GET['class']  : '';
            $method = isset($_GET['method']) ? $_GET['method'] : '';
            
            if ($class && RouteGuard::isAllowedPageClass($class))
            {
                $object = $class == get_class($this) ? $this : new $class;
                if (RouteGuard::isAllowedPageMethod($object, $method))
                {
                    $requestData = array_merge($_GET, $_POST);

                    if (\AuthService::isDemoUser() && \RequestThrottleService::isWriteMethod($method))
                    {
                        \RequestThrottleService::enforceDemoWriteLimit($class, $method);

                        if (\RequestThrottleService::isCreateOperation($method, $requestData))
                        {
                            \RequestThrottleService::enforceDemoCreateLimit($class);
                        }
                    }
                    call_user_func(array($object, $method), $_GET);
                }
            }
        }
        
        parent::show();
    }
}
