<?php

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Request;
use Kantodo\Core\Response;

include_once 'loader/autoload.php';


$APP = new Application();
$APP->registerAuth(new Auth());
$APP->session->start();

// debug
$APP->debugMode();

// TODO: Mobilni verze

if (!Application::configExits()) {
    $APP->router->run([Kantodo\Controllers\InstallController::class, 'installView'], [$APP->request->getPath()]);
    exit;
}

if (file_exists($APP::$PAGES_DIR . '/routes.php')) {
    include $APP::$PAGES_DIR . '/routes.php';
}

// pages
//$APP->router->get('/', [Kantodo\Controllers\DashboardController::class, 'dashboard'], Application::USER);
//$APP->router->get('/team/{teamID}', [Kantodo\Controllers\TeamController::class, 'viewTeam'], Application::USER);
//$APP->router->get('/team/{teamID}/project', [Kantodo\Controllers\ProjectController::class, 'projectsList'], Application::USER);
//$APP->router->get('/team/{teamID}/project/{projID}', [Kantodo\Controllers\ProjectController::class, 'viewProject'], Application::USER);

// actions
//$APP->router->post('/create/team', [Kantodo\Controllers\TeamController::class, 'createTeam'], Application::USER);
//$APP->router->post('/team/{teamID}/create/project', [Kantodo\Controllers\ProjectController::class, 'createProject'], Application::USER);
//$APP->router->post('/project/{projID}/create/column', [Kantodo\Controllers\ColumnController::class, 'createColumn'], Application::USER);
//$APP->router->post('/project/{projID}/create/task', [Kantodo\Controllers\TaskController::class, 'createTask'], Application::USER);

// errors
$APP->router->registerErrorCodeHandler(Application::ERROR_NOT_AUTHORIZED, function (int $role, int $userRole) {
    if (Request::METHOD_POST === Application::$APP->request->getMethod()) {
        Application::$APP->response->setStatusCode(Response::STATUS_CODE_UNAUTHORIZED);
        exit;
    }

    $path = Application::$APP->request->getPath();

    if ($role == Application::ADMIN && $userRole == Application::USER) {
        Application::$APP->response->setLocation();
        exit;
    }

    if ($userRole == Application::GUEST) {
        $path = urlencode($path);
        Application::$APP->response->setLocation("/auth?path={$path}");
        exit;
    }

    Application::$APP->response->setLocation();
    exit;
});

$APP->run();
