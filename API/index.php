<?php

use Kantodo\Auth\Auth;
use Kantodo\API\API;
use Kantodo\API\Controllers\ProjectController;
use Kantodo\API\Controllers\TaskController;
use Kantodo\API\Response;
use Kantodo\Core\BaseApplication;

use function Kantodo\Core\Functions\t;

include "../Loader/autoload.php";


$API = new API();
$API->registerAuth(new Auth());

$session = $API->session;
$session->start();

if (!$session->get('API', false)) 
{
    $session->set('API', ['actionCount' => 1, 'lastAction' => time()]);
} else {
    $info = $session->get('API');
    $t = time() - $info['lastAction'];
    $count = ($info['actionCount'] - floor(($t / 60) * API::$MAX_REQUEST_COUNT_PER_MIN));

    if ($count < 0) {
       $count = 0;
    }

    $info['actionCount'] = $count;
    
    if ($info['actionCount'] >= API::$MAX_REQUEST_COUNT_PER_MIN) 
    {
        $API->response->error(t('api_rate_limit_exceeded', 'api'), Response::STATUS_CODE_TOO_MANY_REQUESTS);
    }
    $session->setInside('API', null, ['actionCount' => ++$info['actionCount'], 'lastAction' => time()]);
}

$API->router->post('create/project', [ProjectController::class, 'create'], BaseApplication::USER);
$API->router->post('create/task', [TaskController::class, 'create'], BaseApplication::USER);

$API->run();

?>