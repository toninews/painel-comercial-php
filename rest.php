<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'bootstrap.php';

use Nexa\Control\RouteGuard;

class NexaRestServer
{
    private static function routeMap()
    {
        return [
            'pessoas.show' => ['PessoaApiService', 'getById'],
        ];
    }

    private static function resolveEndpoint($request)
    {
        $endpoint = isset($request['endpoint']) ? (string) $request['endpoint'] : '';
        $routes = self::routeMap();

        if ($endpoint !== '' && isset($routes[$endpoint]))
        {
            return $routes[$endpoint];
        }

        return null;
    }

    public static function run($request)
    {
        $response = NULL;
        
        try
        {
            $resolved = self::resolveEndpoint($request);

            if ($resolved)
            {
                $response = call_user_func($resolved, $request);
                return json_encode( array('status' => 'success', 'data' => $response));
            }

            // Compatibilidade com clientes legados
            $class  = isset($request['class'])  ? $request['class']  : '';
            $method = isset($request['method']) ? $request['method'] : '';

            if (RouteGuard::isAllowedStaticMethod($class, $method))
            {
                $response = call_user_func(array($class, $method), $request);
                return json_encode( array('status' => 'success', 'data' => $response));
            }
            else
            {
                http_response_code(404);
                $error_message = 'Recurso nao encontrado';
                return json_encode( array('status' => 'error', 'data' => $error_message));
            }
        }
        catch (Exception $e)
        {
            error_log($e);
            http_response_code(500);
            return json_encode( array('status' => 'error', 'data' => $e->getMessage()));
        }
    }
}

print NexaRestServer::run($_REQUEST);
