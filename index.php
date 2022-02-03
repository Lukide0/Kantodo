<?php
declare(strict_types = 1);

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Request;
use Kantodo\Core\Response;

include_once 'loader/autoload.php';

$APP = new Application();
$APP->registerAuth(new Auth());
$APP->session->start();
// REMEMBER: Menit title ve view, preklad cz
// TODO: nastaveni uctu, autocomplete tag, filtrovani ukolu
// TODO: WS!!!!!!!

// 4.2  PA: autocomplete tags + edit task tags
// 5.2  SO-NE: WS
// 7.2  PO: Calendar + Comment JS, VIEWS
// 8.2-?.2 SASS



if (!Application::configExits()) {
    $APP->router->run([Kantodo\Controllers\InstallController::class, 'installView'], [$APP->request->getPath()]);
    exit;
}

if (file_exists($APP::$PAGES_DIR . '/routes.php')) {
    include $APP::$PAGES_DIR . '/routes.php';
}

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
