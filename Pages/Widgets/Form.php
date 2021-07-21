<?php

namespace Kantodo\Widgets;

use Kantodo\Core\Application;
use Kantodo\Core\Request;

class Form
{

    public static function start(string $action = '', string $method = Request::METHOD_POST)
    {
        return "<form action='{$action}' method='{$method}'>";
    }

    public static function tokenCSRF()
    {
        $token = Application::$APP->session->getTokenCSRF();
        return "<input type='hidden' value='{$token}' name='CSRF_TOKEN'>";
    }

    public static function end()
    {
        return '</form>';
    }
}



?>