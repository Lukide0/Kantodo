<?php

use Kantodo\Core\Application;

include_once 'Loader/autoload.php';

$APP = new Application();
$APP->session->start();

$APP->debugMode();


/*
- remove debug
- vytvoření účtu
- komentáře, uvozovky, zavorky
- namespace upravit = use Kantodo/Core/... use Kantodo/Core/... => use Kantodo/Core/{ ... }
- generovani cest pomoci docblock ( @route("/cesta/") ) a json
*/

if (!Application::configExits())
{
    $APP->router->run([Kantodo\Controllers\InstallController::class, 'install'], [$APP->request->getMethod()]);
    exit;
}

// auth
$APP->router->get('/auth', [Kantodo\Controllers\AuthController::class, 'authenticate'], Application::GUEST, true);
$APP->router->get('/auth/sign-out', [Kantodo\Controllers\AuthController::class, 'signOut'], Application::USER);

$APP->router->post('/auth/sign-in', [Kantodo\Controllers\AuthController::class, 'signIn'], Application::GUEST, true);
$APP->router->post('/auth/create', [Kantodo\Controllers\AuthController::class, 'createAccount'], Application::GUEST, true);



// pages
$APP->router->get('/', [Kantodo\Controllers\CalendarController::class, 'today'], Application::USER);
$APP->router->get('/team/{teamID}/', [Kantodo\Controllers\TeamController::class, 'viewTeam'], Application::USER);
$APP->router->get('/team/{teamID}/project', [Kantodo\Controllers\ProjectController::class, 'projectsList'], Application::USER);

// actions
$APP->router->post('/create/team', [Kantodo\Controllers\TeamController::class, 'createTeam'], Application::USER);
$APP->router->post('/team/{teamID}/create/project', [Kantodo\Controllers\ProjectController::class, 'createProject'], Application::USER);


// errors
$APP->router->registerErrorCodeHandler(Application::ERROR_NOT_AUTHORIZED, function(int $role, int $userRole)
{
    $path = Application::$APP->request->getPath();
    
    if ($role == Application::ADMIN && $userRole == Application::USER) 
    {
        Application::$APP->response->setLocation('/');
        exit;
    }
    
    if ($userRole == Application::GUEST) 
    {
        $path = urlencode($path);
        Application::$APP->response->setLocation("/auth?path={$path}");
        exit;
    }
    
    Application::$APP->response->setLocation('/');
    exit;
});

$APP->run();

?>