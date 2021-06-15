<?php

namespace Kantodo\Core;

class Router
{
    public $request;
    
    protected $routes = [
        'get' => [],
        'post' => []
    ];
    
    protected $notFoundRoute = false;
    protected $errorHandler = null;
    protected $response;
   


    public function __construct(Request $r, Response $res) {
        $this->request = $r;
        $this->response = $res;
    }

    public function Get(string $path, $callback, array $access = [0])
    {
        $this->routes['get'][$path] =  ['callback' => $callback, 'access' => $access];
    }

    public function Post(string $path, $callback, array $access = [0])
    {
        $this->routes['post'][$path] = ['callback' => $callback, 'access' => $access];
    }

    public function NotFound($callback)
    {
        $this->notFoundRoute = $callback;
    }

    public function SetErrorHandler($callback)
    {
        $this->errorHandler = $callback;
    }

    public function Resolve()
    {
        $path = $this->request->GetPath();
        $method = $this->request->GetMethod();

        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) 
        {
            if ($this->notFoundRoute !== false)
                call_user_func($this->notFoundRoute);
            
            $this->response->SetStatusCode(404);
            exit;
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

            Application::$APP->controller = $controller;

            try {
                $controller->ExecuteAllMiddlewares();
            } catch (\Throwable $th) {
                if ($this->errorHandler) 
                {
                    call_user_func($this->errorHandler, $th);
                }
                http_response_code($th->getCode());
                return;
            }


            call_user_func([$controller, $classMethod]);
            return;
        }

        call_user_func($callback, $this->request, $this->response);
    }


    public function Run($callback, array $access = [Application::EVERYONE]) 
    {
        if (is_array($callback)) 
        {
            $classMethod = $callback[1];
            $controller = new $callback[0];

            $controller->action = $classMethod;
            $controller->access = $access;

            Application::$APP->controller = $controller;

            try {
                $controller->ExecuteAllMiddlewares();
            } catch (\Throwable $th) {
                if ($this->errorHandler) 
                {
                    call_user_func($this->errorHandler, $th);
                }
                http_response_code($th->getCode());
                exit;
            }


            call_user_func([$controller, $classMethod]);
            return;
        }

        call_user_func($callback);
    }
}



?>