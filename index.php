<?php

use Kantodo\Core\Application;

include_once "config.php";
include_once "loader/load.php";


$APP = new Kantodo\Core\Application();

$APP->router->Get("/", [Kantodo\Controllers\HomeController::class, 'Handle'], [Application::GUEST]);
$APP->Run();

//////////
// TODO //
//////////
/*
 - url/file.extension
 - http access
*/
?>