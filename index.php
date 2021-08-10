<?php

use Kantodo\Core\Application;
use Kantodo\Core\Request;
use Kantodo\Core\Response;

include_once 'Loader/autoload.php';

$APP = new Application();
$APP->session->start();

// debug
$APP->debugMode();

/*
- v akcich response predelat na setStatusCode
- v modelech vrace false pokut prazdne napr. pozice
- remove debug
- vytvoření účtu
- komentáře, uvozovky, zavorky
- namespace upravit = use Kantodo/Core/... use Kantodo/Core/... => use Kantodo/Core/{ ... }
- generovani uml database
- odstranit ziskavani dat ve view
- widget input pouzit
 */

if (!Application::configExits()) {
    $APP->router->run([Kantodo\Controllers\InstallController::class, 'install'], [$APP->request->getMethod()]);
    exit;
}

// auth
$APP->router->get('/auth', [Kantodo\Controllers\AuthController::class, 'authenticate'], Application::GUEST, true);
$APP->router->get('/auth/sign-out', [Kantodo\Controllers\AuthController::class, 'signOut'], Application::USER);

$APP->router->post('/auth/sign-in', [Kantodo\Controllers\AuthController::class, 'signIn'], Application::GUEST, true);
$APP->router->post('/auth/create', [Kantodo\Controllers\AuthController::class, 'createAccount'], Application::GUEST, true);

// pages
$APP->router->get('/', [Kantodo\Controllers\CalendarController::class, 'homePage'], Application::USER);
$APP->router->get('/team/{teamID}', [Kantodo\Controllers\TeamController::class, 'viewTeam'], Application::USER);
$APP->router->get('/team/{teamID}/project', [Kantodo\Controllers\ProjectController::class, 'projectsList'], Application::USER);
$APP->router->get('/team/{teamID}/project/{projID}', [Kantodo\Controllers\ProjectController::class, 'viewProject'], Application::USER);

// actions
$APP->router->post('/create/team', [Kantodo\Controllers\TeamController::class, 'createTeam'], Application::USER);
$APP->router->post('/team/{teamID}/create/project', [Kantodo\Controllers\ProjectController::class, 'createProject'], Application::USER);
$APP->router->post('/project/{projID}/create/column', [Kantodo\Controllers\ColumnController::class, 'createColumn'], Application::USER);
$APP->router->post('/project/{projID}/create/task', [Kantodo\Controllers\TaskController::class, 'createTask'], Application::USER);

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
