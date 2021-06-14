<?php 

namespace Kantodo\Core;

use \InvalidArgumentException;

abstract class Layout 
{
    public abstract function Render(string $content = "", array $params = []);
    public function RenderView(string $view, array $params = []) 
    {
        if (!class_exists($view)) 
        {
            throw new InvalidArgumentException("'$view' is not class");
            exit;
        }

        
        $viewInstance = new $view;
        if (!($viewInstance instanceof IView)) 
        {
            throw new InvalidArgumentException("'$view' class doesn't implements 'IView'");
            exit; 
        }

        ob_start();
        $viewInstance->Render($params);
        $viewHTML = ob_get_clean();

        $this->Render($viewHTML, $params);
    }
}


?>