<?php

declare(strict_types = 1);

namespace Kantodo\Core\Base;

use \InvalidArgumentException;

/**
 * Základ Layout
 */
abstract class Layout
{
    /**
     * Vyrendruje layout s kontentem
     *
     * @param   string  $content  text
     * @param   array<mixed>   $params   parametry
     *
     * @return  void
     */
    abstract public function render(string $content = '', array $params = []);

    /**
     * Vyrendruje layout s view
     *
     * @param   string  $view    view třída
     * @param   array<mixed>   $params  parametry
     *
     * @return  void
     *
     * @throws InvalidArgumentException pokud neexistuje view nebo pokud neimplementuje **'IView'**
     */
    public function renderView(string $view, array $params = [])
    {
        if (!class_exists($view)) {
            throw new InvalidArgumentException("'$view' is not class");
        }

        $viewInstance = new $view;
        if (!($viewInstance instanceof IView)) {
            throw new InvalidArgumentException("'$view' class doesn't implements 'IView'");
        }

        ob_start();
        $viewInstance->render($params);

        /**
         * @var string
         */
        $viewHTML = ob_get_clean();

        $this->render($viewHTML, $params);
    }
}
