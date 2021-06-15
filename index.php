<?php

use Kantodo\Core\Application;

include_once "loader/load.php";


$APP = new Kantodo\Core\Application();


if (!Application::ConfigExits())
{
    $APP->router->Run([Kantodo\Controllers\FrontController::class, 'Install']);
    exit;
}

include_once "config.php";

$APP->Run();

//////////
// TODO //
//////////
/*
 - url/file.extension
 - http access
*/
?>