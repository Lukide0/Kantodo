<?php 

namespace Kantodo\Core;


use InvalidArgumentException;

abstract class Controller
{

    public $action = '';
    public $access = Application::GUEST;

    /**
     * @var BaseMiddleware[]
     */
    protected $middlewares = [];

    public final function registerMiddleware(BaseMiddleware $bm) 
    {
        $this->middlewares[] = $bm;
    }

    public final function executeAllMiddlewares(array $args = []) 
    {
        foreach ($this->middlewares as $middleware) 
        {
            $middleware->execute($args);
        }
    }

    public final function renderView(string $class, array $params = [], string $layout = NULL)
    {

        if ($layout == NULL) 
        {
            call_user_func([new $class, 'Render'], $params);
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


        $layoutInstance->renderView($class, $params);
    }

    public final function renderLayout(string $layout, array $params = []) 
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


        $layoutInstance->render('', $params);

    }

    public final function hasAccess(bool $strict = false)
    {
        $role = Application::getRole();

        if ($strict) 
        {
            if ($role !== $this->access) 
            {
                return false;
            }

            return true;
        }

        if ($role < $this->access)
            return false;
        return true;
    }
}



?>