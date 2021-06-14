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
    protected $response;
   


    public function __construct(Request $r, Response $res) {
        $this->request = $r;
        $this->response = $res;
    }

    public function Get(string $path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function Post(string $path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function NotFound($callback)
    {
        $this->notFoundRoute = $callback;
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

        if (is_array($callback)) 
        {
            $class = new $callback[0];
            $classMethod = $callback[1];

            call_user_func([$class, $classMethod]);
            exit;
        }

        call_user_func($callback);
    }
}



?>