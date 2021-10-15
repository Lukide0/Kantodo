<?php

use Kantodo\Auth\Auth;
use Kantodo\API\API;
use Kantodo\API\Controllers\ProjectController;
use Kantodo\Core\BaseApplication;

include "../Loader/autoload.php";


$API = new API();
$API->registerAuth(new Auth());
$API->session->start();

$API->run();

?>