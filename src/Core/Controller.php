<?php 

namespace Kantodo\Core;

use InvalidArgumentException;

abstract class Controller
{
    public abstract function Handle();

    public function RenderView(string $class, array $params = [], string $layout = null)
    {

        if ($layout == null) 
        {
            call_user_func_array([$class, 'Render'], $params);
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