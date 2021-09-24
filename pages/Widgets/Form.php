<?php

namespace Kantodo\Widgets;

use Kantodo\Core\Application;
use Kantodo\Core\Request;

/**
 * Form element
 */
class Form
{

    /**
     * Začátek form elementu
     *
     * @param   string       $action  akce
     * @param   string       $method  metoda
     *
     * @return  string
     */
    public static function start(string $action = '', string $method = Request::METHOD_POST, string $classes = '')
    {
        return "<form class='{$classes}' action='{$action}' method='{$method}'>";
    }

    /**
     * Input s CSRF token
     *
     * @return  string
     */
    public static function tokenCSRF()
    {
        $token = Application::$APP->session->getTokenCSRF();
        return "<input type='hidden' value='{$token}' name='CSRF_TOKEN'>";
    }

    /**
     * Konec form elementu
     *
     * @return  string
     */
    public static function end()
    {
        return '</form>';
    }
}
