<?php

include_once "config.php";
include_once "loader/load.php";


$APP = new Kantodo\Core\Application();

$APP->router->Get("/", [Kantodo\Controllers\HomeController::class, 'Handle']);
$APP->Run();

//////////
// TODO //
//////////
/*
 - url/file.extension
*/
?>