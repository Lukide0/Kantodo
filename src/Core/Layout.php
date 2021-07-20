<?php 

namespace Kantodo\Core;

use \InvalidArgumentException;

abstract class Layout 
{
    public abstract function render(string $content = "", array $params = []);
    public function renderView(string $view, array $params = []) 
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
        $viewInstance->render($params);
        $viewHTML = ob_get_clean();

        $this->render($viewHTML, $params);
    }
}


?>