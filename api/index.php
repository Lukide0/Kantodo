<?php

use Kantodo\Auth\Auth;
use Kantodo\API\API;
use Kantodo\API\Controllers\AuthController;
use Kantodo\API\Controllers\ProjectController;
use Kantodo\API\Controllers\TaskController;
use Kantodo\Core\Response;

use function Kantodo\Core\Functions\t;

include "../Loader/autoload.php";


$API = new API();
$API->registerAuth(new Auth());

// TODO: odstranit
API::debugMode(true);

$session = $API->session;
$session->start();

$API->router->registerErrorCodeHandler(API::ERROR_NOT_AUTHORIZED, function () {
    API::$API->response->error(t('not_authorized'));
});

$API->router->post('create/project', [ProjectController::class, 'create'], API::USER);
$API->router->post('join/project', [ProjectController::class, 'join'], API::USER);
$API->router->post('create/task', [TaskController::class, 'create'], API::USER);
$API->router->get('get/task/{projectUUID}', [TaskController::class, 'get'], API::USER);
$API->router->get('get/code/{projectUUID}', [ProjectController::class, 'getCode'], API::USER);
$API->router->post('auth/refreshToken', [AuthController::class, 'refreshToken'], API::USER);

$API->run();

?>