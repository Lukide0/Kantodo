<?php

declare(strict_types=1);

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

use function Kantodo\Core\Functions\t;

/**
 * Třída na instalaci
 */
class InstallController extends AbstractController
{
    /**
     * Instalace
     *
     * @param   string $path
     *
     * @return  void
     */
    public function installView(string $path)
    {
        $session = Application::$APP->session;
        $page = 0;
        $action = 'install-database';
        $sectionName = t('database', 'install');

        switch ($path) {
            case 'install-database':
                if ($session->contains('constantsDB') && !$session->contains('constantsStorage')) {
                    Application::$APP->response->setLocation('/install-storage');
                    exit;
                }
                break;
            case 'install-storage':
                if (!$session->contains('constantsDB')) {
                    Application::$APP->response->setLocation('/install-database');
                    exit;
                }

                if ($session->contains('constantsStorage')) {
                    Application::$APP->response->setLocation('/install-admin');
                    exit;
                }

                $page = 1;
                $action = 'install-storage';

                $sectionName = t('storage', 'install');
                break;
            case 'install-admin':
                if (!$session->contains('constantsDB')) {
                    Application::$APP->response->setLocation('/install-database');
                    exit;
                }

                if (!$session->contains('constantsStorage')) {
                    Application::$APP->response->setLocation('/install-storage');
                    exit;
                }


                $page = 2;
                $action = 'install-admin';

                $sectionName = t('account');
                break;
            default:
                break;
        }

        if (Application::$APP->request->getMethod() == Request::METHOD_GET) {
            $this->renderView(InstallView::class, ['page' => $page, 'action' => $action, 'sectionName' => $sectionName], InstallLayout::class);
            exit;
        } else if (Application::$APP->request->getMethod() == Request::METHOD_POST) {
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

    /**
     * Akce instalace db
     *
     * @return  void
     */
    public function installDatabase()
    {
        Application::$INSTALLING = true;
        $session = Application::$APP->session;
        $body = Application::$APP->request->getBody();

        $keys = ['dbName', 'dbHost', 'dbUser'];

        Data::fillEmpty($body[Request::METHOD_POST], ['dbPass', 'dbPrefix'], "");

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $session->addFlashMessage('errors', array_fill_keys($empty, t('empty', 'api')));
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

        if (!$connectionStatus) {
            $session->addFlashMessage('error-msg', t('could_not_connect_to_db', 'install'));
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

        $dbConstants['VERSION'] = $installVersion;

        // vytvoří v db tabulky a provede SQL, které provede dá do souboru "migrations/Versions/{verze}.sql"
        $runner->run($installVersion, true, true);

        Application::$APP->session->set('constantsDB', $dbConstants);
        Application::$APP->response->setLocation("/install-storage");
    }

    /**
     * Akce instalace nastavení konstant
     *
     * @return  void
     */
    public function installStorage()
    {
        $session = Application::$APP->session;
        $body = Application::$APP->request->getBody();

        $keys = ['folderCache', 'folderBackup'];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $session->addFlashMessage('errors', array_fill_keys($empty, t('empty', 'api')));
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        // použití flags např. $notReadable & $notWritable == 0b01 & 0b10 == 0b11
        $notReadable = 0b01;
        $notWritable = 0b10;



        $notDir = [];
        $notPerm = [];
        foreach ($keys as $key) {
            $path = $body[Request::METHOD_POST][$key];

            if (!is_dir($path)) {
                $notDir[] = $key;
                continue;
            }

            if (!is_readable($path)) {
                if (isset($notPerm[$key]))
                    $notPerm[$key] |= $notReadable;
                else
                    $notPerm[$key] = $notReadable;
            }

            if (!is_writable($path)) {
                if (isset($notPerm[$key]))
                    $notPerm[$key] |= $notWritable;
                else
                    $notPerm[$key] = $notWritable;
            }
        }
        $duplicite = Data::duplicate($body[Request::METHOD_POST], $keys, true);

        if (count($notDir) != 0) {
            $session->addFlashMessage('errors', array_fill_keys($notDir, t('is_not_folder', 'install')));
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        if (count($duplicite) != 0) {
            $session->addFlashMessage('errors', array_fill_keys($duplicite, t('folders_can_not_be_the_same', 'install')));
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        if (count($notPerm) != 0) {
            $errors = [];

            foreach ($notPerm as $key => $value) {
                if (($value & $notReadable) == $notReadable && ($value & $notWritable) == $notWritable) {
                    $errors[$key] = t('could_not_read_and_write', 'install');
                } else if (($value & $notReadable) == $notReadable) {
                    $errors[$key] = t('could_not_read', 'install');
                } else {
                    $errors[$key] = t('could_not_write', 'install');
                }
            }
            $session->addFlashMessage('errors', $errors);
            Application::$APP->response->setLocation('/install-storage');
            exit;
        }

        $folderConstants = [
            'STORAGE_CACHE' => "'{$body[Request::METHOD_POST]['folderCache']}'",
            'STORAGE_BACKUP' => "'{$body[Request::METHOD_POST]['folderBackup']}'",
        ];

        Application::$APP->session->set('constantsStorage', $folderConstants);
        Application::$APP->response->setLocation('/install-admin');
    }

    /**
     *  Akce vytvoření admin účtu
     *
     * @return  void
     */
    public function installAdmin()
    {
        Application::$INSTALLING = true;

        $session = Application::$APP->session;
        $body = Application::$APP->request->getBody();

        $keys = ['firstname', 'lastname', 'email', 'password', 'controlPassword'];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);
        if (count($empty) != 0) {
            $session->addFlashMessage('errors', array_fill_keys($empty, t('empty', 'api')));
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        $firstname = Data::formatName($body[Request::METHOD_POST]['firstname']);
        $lastname = Data::formatName($body[Request::METHOD_POST]['lastname']);
        $email = $body[Request::METHOD_POST]['email'];
        $pass = $body[Request::METHOD_POST]['password'];
        $passControl = $body[Request::METHOD_POST]['controlPassword'];

        if (!Data::isValidEmail($email)) {
            $session->addFlashMessage('errors', ['email' => t('email_is_not_valid', 'auth')]);
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        if ($pass != $passControl) {
            $session->addFlashMessage('errors', ['password' => t('passwords_do_not_match', 'auth'), 'controlPassword' => t('passwords_do_not_match', 'auth')]);
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        if ($firstname == false) {
            $session->addFlashMessage('errors', ['firstname' => t('firstname_is_not_valid')]);
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        if ($lastname == false) {
            $session->addFlashMessage('errors', ['lastname' => t('lastname_is_not_valid')]);
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        if (!Data::isValidPassword($pass, true, true, true)) {
            $session->addFlashMessage('errors', ['password' => t('password_is_not_valid')]);
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

        Application::$DB_TABLE_PREFIX = DB_TABLE_PREFIX;

        $pass = Auth::hashPassword($pass, $email);

        $userModel = new UserModel();
        $status = $userModel->create($firstname, $lastname, $email, $pass);


        if ($status == false) {
            $session->addFlashMessage('error-msg', t('something_went_wront', 'api'));
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        [$id] = $status;

        if ($userModel->addMeta('position', 'admin', $id) == false) {
            $userModel->delete($id);

            $session->addFlashMessage('error-msg', t('something_went_wront', 'api'));
            Application::$APP->response->setLocation('/install-admin');
            exit;
        }

        $constantsDB = array_map(function ($value) {
            return "'{$value}'";
        }, Application::$APP->session->get('constantsDB'));
        $constantsFolder = Application::$APP->session->get('constantsStorage');

        Application::createSymmetricKey();
        Application::createAsymmetricSecretKey();

        $constants = array_merge($constantsDB, $constantsFolder);

        Application::overrideConfig($constants);

        Application::$APP->session->destroy();

        Application::$APP->response->setLocation();
    }
}
