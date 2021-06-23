<?php 

namespace Kantodo\Core;

use InvalidArgumentException;

abstract class Controller
{

    public $action = '';
    public $access = [];

    /**
     * @var BaseMiddleware[]
     */
    protected $middlewares = [];

    public function RegisterMiddleware(BaseMiddleware $bm) 
    {
        $this->middlewares[] = $bm;
    }

    public function ExecuteAllMiddlewares() 
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->Execute();
        }
    }

    public function RenderView(string $class, array $params = [], string $layout = null)
    {

        if ($layout == null) 
        {
            call_user_func_array([new $class, 'Render'], $params);
            return;
        }

        if (!class_exists($layout)) 
        {
            throw new InvalidArgumentException("'$layout' is not class");
            exit;
        }

        
        $layoutInstance = new $layout;
        if (!($layoutInstance instanceof Layout)) 
        {
            throw new InvalidArgumentException("'$layout' class doesn't extends 'Layout'");
            exit; 
        }


        $layoutInstance->RenderView($class, $params);
    }

    public function RenderLayout(string $layout, array $params = []) 
    {
        if (!class_exists($layout)) 
        {
            throw new InvalidArgumentException("'$layout' is not class");
            exit;
        }

        
        $layoutInstance = new $layout;
        if (!($layoutInstance instanceof Layout)) 
        {
            throw new InvalidArgumentException("'$layout' class doesn't extends 'Layout'");
            exit; 
        }


        $layoutInstance->Render("", $params);

    }
}



?>