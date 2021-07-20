<?php

namespace Kantodo\Core;

use Exception;
use Kantodo\Core\Exception\KantodoException;
use Kantodo\Core\Exception\NotAuthorizedException;
use Kantodo\Core\Exception\NotFoundException;

class Router
{
    public $request;
    
    protected $routes = [
        'get' => [],
        'post' => []
    ];
    
    protected $errorHandlers = [];
    protected $response;
   


    public function __construct(Request $r, Response $res) {
        $this->request = $r;
        $this->response = $res;
    }

    public function get(string $path, $callback, int $access = Application::GUEST, bool $strict = false)
    {
        $this->routes['get'][$path] =  ['callback' => $callback, 'access' => $access, 'strict' => $strict ];
    }

    public function post(string $path, $callback, int $access = Application::GUEST, bool $strict = false)
    {
        $this->routes['post'][$path] = ['callback' => $callback, 'access' => $access, 'strict' => $strict];
    }

    public final function registerErrorCodeHandler(int $code, $callback)
    {
        $this->errorHandlers["{$code}"] = $callback;
    }

    public final function handleErrorCode(int $code, array $params = []) 
    {
        if (isset($this->errorHandlers["{$code}"]))
        {
            call_user_func_array($this->errorHandlers["{$code}"], $params);
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
        $callback = $this->routes[$method][$path] ?? false;

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

            try {
                $controller->executeAllMiddlewares();
            } catch (KantodoException $ex) {
                $controller->handleMiddlewareError($ex);
                return;
            }


            call_user_func([$controller, $classMethod]);
            return;
        }
        call_user_func($callback['callback'], $this->request, $this->response);
    }
}



?>