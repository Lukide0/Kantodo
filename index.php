<?php

use Kantodo\Core\Application;


include_once "Loader/load.php";

$APP = new Kantodo\Core\Application();
$APP->session->start();

/*
- remove debug
- sjednotit uvozovky
- sjednotit názvy cameCase
- vytvoření účtu
- 
*/
$APP->debugMode();

if (!Application::configExits())
{
    $APP->router->run([Kantodo\Controllers\InstallController::class, 'Install'], [$APP->request->getMethod()]);
    exit;
}

// auth

$APP->router->get("/auth", [Kantodo\Controllers\AuthController::class, "Authenticate"], Application::GUEST, true);



$APP->router->post("/auth/sign-in", [Kantodo\Controllers\AuthController::class, "SignIn"], Application::GUEST, true);
$APP->router->post("/auth/create", [Kantodo\Controllers\AuthController::class, "CreateAccount"], Application::GUEST, true);

$APP->router->get("/auth/sign-out", [Kantodo\Controllers\AuthController::class, "SignOut"], Application::USER);


// main page = projects list

$APP->router->get("/", [Kantodo\Controllers\ProjectController::class, 'ProjectsList'], Application::USER);



// errors

$APP->router->registerErrorCodeHandler(Application::ERROR_NOT_AUTHORIZED, function(int $role, int $userRole)
{
    $path = Application::$APP->request->getPath();
    $path = urlencode($path);
    
    if ($role == Application::ADMIN && $userRole == Application::USER) 
    {
        Application::$APP->response->setLocation("/");
        exit;
    }
    
    if ($userRole == Application::GUEST) 
    {
        Application::$APP->response->setLocation("/auth?path={$path}");
        exit;
    }
    
    Application::$APP->response->setLocation("/");
    exit;
});

$APP->run();

?>