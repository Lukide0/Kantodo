<?php

use Kantodo\Core\Application;


include_once 'Loader/autoload.php';

$APP = new Kantodo\Core\Application();
$APP->session->start();

/*
- remove debug
- sjednotit uvozovky
- vytvoření účtu
- 
*/
$APP->debugMode();

if (!Application::configExits())
{
    $APP->router->run([Kantodo\Controllers\InstallController::class, 'install'], [$APP->request->getMethod()]);
    exit;
}

// auth

$APP->router->get('/auth', [Kantodo\Controllers\AuthController::class, 'authenticate'], Application::GUEST, true);

$APP->router->post('/auth/sign-in', [Kantodo\Controllers\AuthController::class, 'signIn'], Application::GUEST, true);
$APP->router->post('/auth/create', [Kantodo\Controllers\AuthController::class, 'createAccount'], Application::GUEST, true);

$APP->router->get('/auth/sign-out', [Kantodo\Controllers\AuthController::class, 'signOut'], Application::USER);


// main page = projects list

$APP->router->get('/', [Kantodo\Controllers\ProjectController::class, 'projectsList'], Application::USER);


// actions

$APP->router->post('/create/team', [Kantodo\Controllers\TeamController::class, 'createTeam'], Application::USER);



// errors

$APP->router->registerErrorCodeHandler(Application::ERROR_NOT_AUTHORIZED, function(int $role, int $userRole)
{
    $path = Application::$APP->request->getPath();
    $path = urlencode($path);
    
    if ($role == Application::ADMIN && $userRole == Application::USER) 
    {
        Application::$APP->response->setLocation('/');
        exit;
    }
    
    if ($userRole == Application::GUEST) 
    {
        Application::$APP->response->setLocation('/auth?path={$path}');
        exit;
    }
    
    Application::$APP->response->setLocation('/');
    exit;
});

$APP->run();

?>