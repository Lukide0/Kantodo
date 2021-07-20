<?php

namespace Kantodo\Widgets;

use Kantodo\Core\Application;

class Form
{

    public static function start(string $action = "", string $method = "post")
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
        return "</form>";
    }
}



?>