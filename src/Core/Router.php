<?php

namespace Kantodo\Core;

use Kantodo\Core\Exception\KantodoException;

class Router
{
    public $request;
    
    protected $routes = [
        Request::METHOD_GET => [],
        Request::METHOD_POST => []
    ];
    
    protected $errorHandlers = [];
    protected $response;
   


    public function __construct(Request $r, Response $res) {
        $this->request = $r;
        $this->response = $res;
    }

    public function get(string $path, $callback, int $access = Application::GUEST, bool $strict = false)
    {
        $this->routes[Request::METHOD_GET][$path] =  ['callback' => $callback, 'access' => $access, 'strict' => $strict ];
    }

    public function post(string $path, $callback, int $access = Application::GUEST, bool $strict = false)
    {
        $this->routes[Request::METHOD_POST][$path] = ['callback' => $callback, 'access' => $access, 'strict' => $strict];
    }

    public function match(string $path, string $method)
    {

        $routes = $this->routes[$method] ?? [];

        if (isset($routes[$path]))
            return [$routes[$path], []];

        $path = ltrim($path, '/');
        $pathParts = explode('/', $path);
        $pathPartsCount = count($pathParts);

        foreach ($routes as $route => $callback) 
        {
            $route = ltrim($route, '/');
            $routeParts = explode('/', $route);

            if (count($routeParts) != $pathPartsCount)
                continue;

            $tmp = [];
            for ($i=0; $i < $pathPartsCount; $i++) 
            {


                if (strlen($routeParts[$i]) > 0 && $routeParts[$i][0] == '{') 
                {
                    $tmp[trim($routeParts[$i], "{}")] = $pathParts[$i];
                    continue;
                }
                elseif ($routeParts[$i] !== $pathParts[$i]) 
                    continue 2;                
            }
            
            return [$callback, $tmp];
        }
        return [false, []];


    }

    public final function registerErrorCodeHandler(int $code, $callback)
    {
        $this->errorHandlers[$code] = $callback;
    }

    public final function handleErrorCode(int $code, array $params = []) 
    {
        if (isset($this->errorHandlers[$code]))
        {
            call_user_func_array($this->errorHandlers[$code], $params);
            return;
        }
        http_response_code($code);
    }

    public function setErrorHandler($callback)
    {
        $this->errorHandler = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $this->runPath($path, $method);
    }


    public function run($callback, array $params = []) 
    {
        if (is_array($callback)) 
        {
            $classMethod = $callback[1];
            $controller = new $callback[0];

            $controller->action = $classMethod;
            Application::$APP->controller = $controller;

            try {
                $controller->executeAllMiddlewares();
            } catch (\Throwable $th) {
                if ($this->errorHandler) 
                {
                    call_user_func($this->errorHandler, $th);
                }
                http_response_code($th->getCode());
                exit;
            }
            call_user_func_array([$controller, $classMethod], $params);
            return;
        }

        call_user_func_array($callback, $params);
    }

    public function runPath(string $path, string $method = Request::METHOD_GET)
    {
        [$callback, $params] = $this->match($path, $method);

        if ($callback === false) 
        {
            $this->handleErrorCode(Application::ERROR_NOT_FOUND);
            return;
        }
        if (is_array($callback['callback'])) 
        {
            $classMethod = $callback['callback'][1];
            
            /**
             * @var Controller
             */
            $controller = new $callback['callback'][0];
            
            $controller->action = $classMethod;
            $controller->access = $callback['access'];
            
            if (!$controller->hasAccess($callback['strict'])) 
            {
                $this->handleErrorCode(Application::ERROR_NOT_AUTHORIZED, [$callback['access'], Application::getRole(), $callback['strict']]);
                return;
            }
            

            Application::$APP->controller = $controller;

            $controller->executeAllMiddlewares($params);

            call_user_func([$controller, $classMethod], $params);
            return;
        }
        call_user_func($callback['callback'], $this->request, $this->response, $params);
    }
}



?>