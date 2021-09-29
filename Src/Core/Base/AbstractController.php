<?php

namespace Kantodo\Core\Base;

use InvalidArgumentException;
use Kantodo\Core\Application;

abstract class AbstractController
{

    /**
     * Akce
     *
     * @var string
     */
    public $action = '';

    /**
     * Přístupnost
     *
     * @var int
     */
    public $access = Application::GUEST;

    /**
     * @var AbstractMiddleware[]
     */
    protected $middlewares = [];

    /**
     * Registruje middleware
     *
     * @param   AbstractMiddleware  $am  middleware
     *
     * @return  void
     */
    final public function registerMiddleware(AbstractMiddleware $am)
    {
        $this->middlewares[] = $am;
    }

    /**
     * Vykonná všechny middleware
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    final public function executeAllMiddlewares(array $params = [])
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->execute($params);
        }
    }

    /**
     * Vyrendruje view
     *
     * @param   string  $class   view třída
     * @param   array<mixed>   $params  parametry
     * @param   string  $layout  layout třída
     *
     * @return  void
     *
     * @throws InvalidArgumentException pokud layout neexistuje nebo není **'Layout'**
     * 
     */
    final public function renderView(string $class, array $params = [], string $layout = null)
    {
        if ($layout == null) {
            /* @phpstan-ignore-next-line */
            call_user_func([new $class, 'Render'], $params);
            return;
        }

        if (!class_exists($layout)) {
            throw new InvalidArgumentException("'$layout' is not class");
        }

        $layoutInstance = new $layout;
        if (!($layoutInstance instanceof Layout)) {
            throw new InvalidArgumentException("'$layout' class doesn't extends 'Layout'");
        }

        $layoutInstance->renderView($class, $params);
    }

    /**
     * Vyrendruje layout
     *
     * @param   string  $layout  layout třída
     * @param   array<mixed>   $params  parametry
     *
     * @return  void
     *
     * @throws InvalidArgumentException pokud layout neexistuje nebo není **'Layout'**
     */
    final public function renderLayout(string $layout, array $params = [])
    {
        if (!class_exists($layout)) {
            throw new InvalidArgumentException("'$layout' is not class");
        }

        $layoutInstance = new $layout;
        if (!($layoutInstance instanceof Layout)) {
            throw new InvalidArgumentException("'$layout' class doesn't extends 'Layout'");
        }

        $layoutInstance->render('', $params);
    }

    /**
     * Zjistí jestli mám uživatel přístup
     *
     * @param   bool   $strict  false => všichni uživatelé s vyšší pozicí, true => pouze uživatelé s určenou pozicí
     *
     * @return  bool
     */
    final public function hasAccess(bool $strict = false)
    {
        $role = Application::getRole();

        if ($strict) {
            if ($role !== $this->access) {
                return false;
            }

            return true;
        }

        if ($role < $this->access) {
            return false;
        }

        return true;
    }
}
