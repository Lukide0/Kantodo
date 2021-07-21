<?php 

namespace Kantodo\Core;


use InvalidArgumentException;
use Kantodo\Core\Exception\KantodoException;
use Kantodo\Core\Exception\NotAuthorizedException;

abstract class Controller
{

    public $action = '';
    public $access = Application::GUEST;
    public $middlewareErrorHandlers = [];

    /**
     * @var BaseMiddleware[]
     */
    protected $middlewares = [];

    public final function registerMiddleware(BaseMiddleware $bm) 
    {
        $this->middlewares[] = $bm;
    }

    public final function executeAllMiddlewares() 
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->execute();
        }
    }

    public final function registerMiddlewareErrorHandler(string $errorName, $callback)
    {
        $this->middlewareErrorHandlers[$errorName] = $callback;
    }

    public final function handleMiddlewareError(KantodoException $exception) 
    {
        $errorName = get_class($exception);

        if (isset($this->middlewareErrorHandlers[$errorName])) 
        {
            call_user_func($this->middlewareErrorHandlers[$errorName], $exception);
            return;
        }

        http_response_code($exception->getCode());
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