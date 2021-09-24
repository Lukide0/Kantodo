<?php

namespace Kantodo\Controllers;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Database\Migration\Runner;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Core\Validation\Data;
use Kantodo\Models\UserModel;
use Kantodo\Views\InstallView;
use Kantodo\Views\Layouts\InstallLayout;

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
    public function installView(string $path)
    {
        // TODO: znemoznit preskoceny kroku instalace
        $page = 0;
        $action = 'install-database';
        switch ($path) {
            case 'install-database':
                $page = 0;
                break;
            case 'install-storage':
                $page = 1;
                $action = 'install-storage';
                break;
            case 'install-admin':
                $page = 2;
                $action = 'install-admin';
                break;
            default:
                break;
        }

        if (Application::$APP->request->getMethod() == Request::METHOD_GET) {
            $this->renderView(InstallView::class, ['page' => $page, 'action' => $action], InstallLayout::class);
            exit;
        }
        else if (Application::$APP->request->getMethod() == Request::METHOD_POST) {
            switch ($path) {
                case 'install-database':
                    $this->installDatabase();
                    break;
                case 'install-storage':
                    $this->installStorage();
                    break;
                case 'install-admin':
                    $this->installAdmin();
                    break;
                default:
                    Application::$APP->response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
                    break;
            }
        }

    }

    public function installDatabase() 
    {
        Application::$INSTALLING = true;
        $body = Application::$APP->request->getBody();

        $keys = ['dbName', 'dbHost', 'dbUser'];

        Data::fillEmpty($body[Request::METHOD_POST], ['dbPass', 'dbPrefix'], "");

        // TODO: frontend error
        if (Data::isEmpty($body[Request::METHOD_POST], $keys)) {
            Application::$APP->response->setLocation();
            exit;
        }
        $dbName = $body[Request::METHOD_POST]['dbName'];
        $dbHost = $body[Request::METHOD_POST]['dbHost'];
        $dbUser = $body[Request::METHOD_POST]['dbUser'];
        $dbPass = $body[Request::METHOD_POST]['dbPass'];
        $dbPrefix = $body[Request::METHOD_POST]['dbPrefix'];

        /**
         * Pokus připojení k databázi
         *
         * @var bool
         */
        $connectionStatus = Connection::tryConnect("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);

        // TODO: frontend error
        if (!$connectionStatus) {
            Application::$APP->response->setLocation();
            exit;
        }


        // TMP konstanty
        define('DB_NAME', $dbName);
        define('DB_HOST', $dbHost);
        define('DB_USER', $dbUser);
        define('DB_PASS', $dbPass);
        define('DB_TABLE_PREFIX', $dbPrefix);

        // konstanty do configu
        $dbConstants = [
            'DB_HOST'         => $dbHost,
            'DB_NAME'         => $dbName,
            'DB_USER'         => $dbUser,
            'DB_PASS'         => $dbPass,
            'DB_TABLE_PREFIX' => $dbPrefix,
        ];

        // manuální nastavení předpony
        Application::$DB_TABLE_PREFIX = $dbPrefix;

        $runner = new Runner();

        $installVersion = $runner->getInstallVersion();

        // vytvoří v db tabulky a celé sql, které provede dá do souboru "migrations/Versions/{verze}.sql"
        $runner->run($installVersion, true, true);

        Application::$APP->session->set('constantsDB', $dbConstants);
        Application::$APP->response->setLocation("/install-storage");

    }

    public function installStorage()
    {
        $body = Application::$APP->request->getBody();
    
        $keys = ['folderData', 'folderCache', 'folderTmp', 'folderBackup'];

        // TODO: frontend error
        if (Data::isEmpty($body[Request::METHOD_POST], $keys)) {
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        $notDir = [];
        $notPerm = [];
        foreach ($keys as $key) {
            $path = $body[Request::METHOD_POST][$key];

            if (!is_dir($path))
                $notDir[] = $key;

            if (!is_readable($path))
                $notPerm[] = ['read', $key];
            
            if (!is_writable($path))
                $notPerm[] = ['write', $key];
        }

        $duplicite = Data::duplicate($body[Request::METHOD_POST], $keys, true);
        
        // TODO: frontend error
        if (count($notDir) != 0) {
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        // TODO: frontend error
        if (count($duplicite) != 0) {
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        // TODO: frontend error
        if (count($notPerm) != 0) {
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        $folderConstants = [
            'STORAGE_DATA' => "'{$body[Request::METHOD_POST]['folderData']}'",
            'STORAGE_TMP' => "'{$body[Request::METHOD_POST]['folderTmp']}'",
            'STORAGE_CACHE' => "'{$body[Request::METHOD_POST]['folderCache']}'",
            'STORAGE_BACKUP' => "'{$body[Request::METHOD_POST]['folderBackup']}'"
        ];

        Application::$APP->session->set('constantsStorage', $folderConstants);
        Application::$APP->response->setLocation('/install-admin');
    }
    
    public function installAdmin()
    {
        Application::$INSTALLING = true;

        $body = Application::$APP->request->getBody();
    
        $keys = ['firstname', 'lastname', 'email', 'password', 'controlPassword'];

        // TODO: frontend error
        if (Data::isEmpty($body[Request::METHOD_POST], $keys)) {
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        $firstname = Data::formatName($body[Request::METHOD_POST]['firstname']);
        $lastname = Data::formatName($body[Request::METHOD_POST]['lastname']);
        $email = $body[Request::METHOD_POST]['email'];
        $pass = $body[Request::METHOD_POST]['password'];
        $passControl = $body[Request::METHOD_POST]['controlPassword'];

        // TODO: frontend error
        if (!Data::isValidEmail($email)){
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        // TODO: frontend error
        if ($pass != $passControl) {
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        // TODO: frontend error
        if (!Data::isValidPassword($pass, true, true, true)) {
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        $dbConstants = Application::$APP->session->get('constantsDB');

        
        // TMP konstanty
        define('DB_NAME', $dbConstants['DB_NAME']);
        define('DB_HOST', $dbConstants['DB_HOST']);
        define('DB_USER', $dbConstants['DB_USER']);
        define('DB_PASS', $dbConstants['DB_PASS']);
        define('DB_TABLE_PREFIX', $dbConstants['DB_TABLE_PREFIX']);

        
        $con = Connection::getInstance();

        $pass = Auth::hashPassword($pass, $email);

        $userModel = new UserModel();
        $id = $userModel->create($firstname, $lastname, $email, $pass);

        // TODO: frontend error
        if ($id == false) {
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        // TODO: frontend error
        if ($userModel->addMeta('position', 'admin', $id) == false) {
            $userModel->delete($id);

            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        $constantsDB = array_map(function($value) { return "'{$value}'";}, Application::$APP->session->get('constantsDB'));
        $constantsFolder = Application::$APP->session->get('constantsStorage');
        
        $constants = array_merge($constantsDB, $constantsFolder);

        Application::overrideConfig($constants);

        Application::$APP->session->destroy();

        Application::$APP->response->setLocation();
        
    }
}
