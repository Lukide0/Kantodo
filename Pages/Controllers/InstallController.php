<?php

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Database\Connection;

use Kantodo\Core\Database\Migration\Runner;
use Kantodo\Core\Validation\Data;

use Kantodo\Models\ProjectModel;
use Kantodo\Models\TeamModel;
use Kantodo\Models\UserModel;
use Kantodo\Views\InstallView;

/**
 * Třída na instalaci
 */
class InstallController extends AbstractController
{
    /**
     * Instalace
     *
     * @param   string $method
     *
     * @return  void
     */
    public function install($method = 'get')
    {
        // "browser"
        if ($method == 'get') {
            $this->renderView(InstallView::class);
        }

        // akce
        else if ($method == 'post') {
            $this->installAction();
        }

    }

    /**
     * Akce na instalaci
     *
     * @return  void
     */
    private function installAction()
    {

        Application::$INSTALLING = true;

        $body = Application::$APP->request->getBody();

        $keys = ['dbName', 'dbUser', 'dbHost', 'adminName', 'adminSurname', 'adminEmail', 'adminPass'];

        /**
         * Prázdné klíče
         *
         * @var string[]
         */
        $emptyKeys = Data::empty($body['post'], $keys);

        Data::setIfNotSet($body['post'], ['dbPass', 'dbPrefix'], '');

        if (count($emptyKeys) != 0) {
            Application::$APP->response->addResponseError('Empty field|s');
            Application::$APP->response->outputResponse();
            exit;
        }

        /**
         * Pokus připojení k databázi
         *
         * @var bool
         */
        $connectionStatus = Connection::tryConnect("mysql:host={$body['post']['dbHost']};dbname={$body['post']['dbName']}", $body['post']['dbUser'], $body['post']['dbPass'] ?? '');

        if (!$connectionStatus) {
            Application::$APP->response->addResponseError('Could not connect to database');
            Application::$APP->response->outputResponse();
            exit;
        }

        // konstanty v config.php
        $dbConstants = [
            'DB_HOST'         => "'{$body['post']['dbHost']}'",
            'DB_NAME'         => "'{$body['post']['dbName']}'",
            'DB_USER'         => "'{$body['post']['dbUser']}'",
            'DB_PASS'         => "'{$body['post']['dbPass']}'",
            'DB_TABLE_PREFIX' => "'{$body['post']['dbPrefix']}'",
        ];

        // validace
        $adminFirstname = Data::formatName($body['post']['adminName']);
        $adminLastname  = Data::formatName($body['post']['adminSurname']);
        $adminEmail     = filter_var($body['post']['adminEmail'], FILTER_SANITIZE_EMAIL);
        $adminPass      = $body['post']['adminPass'];

        if ($adminFirstname === false) {
            Application::$APP->response->addResponseError('Invalid firstname');
            Application::$APP->response->outputResponse();
            exit;
        }

        if ($adminLastname === false) {
            Application::$APP->response->addResponseError('Invalid lastname');
            Application::$APP->response->outputResponse();
            exit;
        }

        if (!Data::isValidEmail($adminEmail)) {
            Application::$APP->response->addResponseError('Invalid email');
            Application::$APP->response->outputResponse();
            exit;
        }

        if (!Data::isValidPassword($adminPass, true, true, true)) {
            Application::$APP->response->addResponseError('Invalid password');
            Application::$APP->response->outputResponse();
            exit;
        }

        $adminPassHash = Data::hashPassword($adminPass, $adminEmail);

        // manuální nastavení prefix
        Application::$DB_TABLE_PREFIX = $body['post']['dbPrefix'];

        // tmp konstanty
        define('DB_HOST', $body['post']['dbHost']);
        define('DB_NAME', $body['post']['dbName']);
        define('DB_USER', $body['post']['dbUser']);
        define('DB_PASS', $body['post']['dbPass']);

        $migRunner = new Runner();

        // instalace stažené verze
        $migRunner->run($migRunner->getInstallVersion());

        // vytvoření admina
        $userModel = new UserModel();
        $userId    = $userModel->create($adminFirstname, $adminLastname, $adminEmail, $adminPassHash);

        if ($userId === false) {
            Application::$APP->response->addResponseError('Admin account was not created.');
            Application::$APP->response->outputResponse();
            exit;
        }

        $metaId = $userModel->addMeta('position', 'admin', $userId);

        if ($metaId === false) {
            $userModel->delete($userId);
            Application::$APP->response->addResponseError('Admin account was not created.');
            Application::$APP->response->outputResponse();
            exit;
        }

        ///////////////////////////
        // VYTVOŘENÍ VŠECH POZIC //
        ///////////////////////////

        $teamModel    = new TeamModel();
        $projectModel = new ProjectModel();
        $status       = null;

        // pozice v týmu
        foreach (TeamModel::POSITIONS as $name => $priv) {
            $status = $teamModel->createPosition($name);
            if ($status === false) {
                break;
            }

        }

        // pozice v projektu
        foreach (ProjectModel::POSITIONS as $name => $priv) {
            $status = $projectModel->createPosition($name);
            if ($status === false) {
                break;
            }

        }

        if ($status === false) {
            $migRunner->run("0_0");
            Application::$APP->response->addResponseError('Server error');
            Application::$APP->response->outputResponse();
        }

        // vytvoření config.php
        Application::overrideConfig($dbConstants);

        Application::$APP->response->setResponseData(true);
        Application::$APP->response->outputResponse();
    }
}
