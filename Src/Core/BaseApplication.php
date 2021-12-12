<?php

declare(strict_types = 1);

namespace Kantodo\Core;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Database\Connection;
use ParagonIE\Paseto\Protocol\Version4;

use const Kantodo\Core\Functions\FILE_FLAG_CREATE_DIR;
use const Kantodo\Core\Functions\FILE_FLAG_OVERRIDE;

use function Kantodo\Core\Functions\filePutContentSafe;

/**
 * Aplikace
 */
class BaseApplication
{

    // role
    const GUEST = 0;
    const USER  = 1;
    const ADMIN = 2;

    // error
    const ERROR_NOT_AUTHORIZED = 401;
    const ERROR_NOT_FOUND      = 404;

    /**
     * @var BaseApplication
     */
    public static $BASE_APP;

    /**
     * Cesta k aplikaci
     *
     * @var string
     */
    public static $ROOT_DIR;

    /**
     * Cesta k aplikaci v url
     *
     * @var string
     */
    public static $URL_PATH;

    /**
     * Cesta k datům
     *
     * @var string
     */
    public static $DATA_PATH;

    /**
     * Cesta k klíčům
     *
     * @var string
     */
    public static $KEYS_PATH;
    /**
     * Předpona tabulek
     *
     * @var string
     */
    public static $DB_TABLE_PREFIX = '';

    /**
     * @var bool
     */
    public static $DEBUG_MODE = false;

    /**
     * @var bool
     */
    public static $CONFIG_LOADED = false;

    /**
     * @var IAuth
     */
    private static $AUTH;

    /**
     * @var int
     */
    private $userRole = null;

    /**
     * @var Request
     */
    public $request;
    /**
     * @var Response
     */
    public $response;
    /**
     * @var AbstractController
     */
    public $controller;
    /**
     * @var Session
     */
    public $session;

    /**
     * Jazyk
     *
     * @var string
     */
    public static $LANG = 'en';

    /**
     * @var Lang
     */
    public $lang;

    /**
     * @var array<string,array<callable>>
     */
    private $eventListeners = [];

    public function __construct()
    {
        self::$BASE_APP = $this;

        self::$ROOT_DIR = dirname(
            // src dir
            dirname(
                // core dir
                __DIR__
            )
        );

        self::$URL_PATH  = str_replace($_SERVER['DOCUMENT_ROOT'], '', (str_replace('\\', '/', self::$ROOT_DIR)));
        self::$DATA_PATH = self::$ROOT_DIR . '/data/';
        self::$KEYS_PATH = self::$ROOT_DIR . '/App/Keys/';

        $this->request  = new Request();
        $this->response = new Response();
        $this->session  = new Session();
        $this->lang     = new Lang();

        if (self::configExits()) {
            $this->loadConfig();
        }
    }

    /**
     * Převede absolutní cestu do url
     *
     * @param   string  $path  cesta
     *
     * @return  string
     */
    public static function systemPathToUrl(string $path)
    {
        /** @phpstan-ignore-next-line */
        return str_replace(self::$ROOT_DIR, self::$URL_PATH, $path, 1);
    }

    /**
     * Nastaví debug mod
     *
     * @param   bool  $enable  zapnout
     *
     * @return  void
     */
    public static function debugMode(bool $enable = true)
    {
        // application
        self::$DEBUG_MODE = $enable;

        // database
        if (self::$CONFIG_LOADED) {
            Connection::debugMode();
        }
    }

    /**
     * Načte config.php
     *
     * @return  void
     */
    public function loadConfig()
    {
        if (self::$CONFIG_LOADED) {
            return;
        }

        include self::$ROOT_DIR . '/config.php';
        self::$CONFIG_LOADED = true;

        /** @phpstan-ignore-next-line */
        self::$DB_TABLE_PREFIX = DB_TABLE_PREFIX;
    }

    /**
     * Registruje event callback
     *
     * @param   string  $eventName  jméno eventu
     * @param   mixed  $callback    callback
     *
     * @return  void
     */
    public function on(string $eventName, $callback)
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    /**
     * Provede event
     *
     * @param   string  $eventName  jméno eventu
     *
     * @return void
     */
    public function trigger(string $eventName)
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    /**
     * Vytvoří symetrický klíč
     *
     * @return  string klíč
     */
    public static function createSymmetricKey() 
    {
        $key = Version4::generateSymmetricKey()->raw();

        filePutContentSafe(Application::$KEYS_PATH . 'symmetric.key', $key, FILE_FLAG_OVERRIDE | FILE_FLAG_CREATE_DIR);

        return $key;
    }


    /**
     * Vytvoří asymetrický soukromý klíč
     *
     * @return  string klíč
     */
    public static function createAsymmetricSecretKey() 
    {
        $key = Version4::generateAsymmetricSecretKey()->raw();

        filePutContentSafe(Application::$KEYS_PATH . 'asymmetric_secret.key', $key, FILE_FLAG_OVERRIDE | FILE_FLAG_CREATE_DIR);

        return $key;
    }


    /**
     * Načte nebo vytvoří symetrický klíč
     *
     * @return  string|false  klíč, false v případě, že se nepodařilo přečíst soubor
     */
    public static function getSymmetricKey()
    {
        $path = Application::$KEYS_PATH . 'symmetric.key';
        if (file_exists($path))
            return file_get_contents($path);
        else
            return self::createSymmetricKey();
    }

    /**
     * Načte nebo vytvoří asymetrický soukromý klíč
     *
     * @return  string|false  klíč, false v případě, že se nepodařilo přečíst soubor
     */
    public static function getAsymmetricSecretKey()
    {
        $path = Application::$KEYS_PATH . 'asymmetric_secret.key';
        if (file_exists($path))
            return file_get_contents($path);
        else
            return self::createAsymmetricSecretKey();
    }


    /**
     * Zkontroluje jestli existuje config.php
     *
     * @return  bool
     */
    public static function configExits()
    {
        return file_exists(self::$ROOT_DIR . '/config.php');
    }

    /**
     * Registruje modul Auth
     *
     * @param   IAuth  $auth
     *
     * @return  void
     */
    public function registerAuth(IAuth $auth)
    {
        self::$AUTH = $auth;
    }

    /**
     * Získá roli uživatele
     *
     * @return  int  role
     */
    public static function getRole()
    {
        if (self::$BASE_APP->userRole !== null) {
            return self::$BASE_APP->userRole;
        }

        /** @phpstan-ignore-next-line */
        if (isset(self::$AUTH) && !self::$AUTH::isLogged()) {
            self::$BASE_APP->userRole = self::GUEST;
            return self::GUEST;
        }

        return self::$BASE_APP->userRole = self::$BASE_APP->session->get('user')['role'] ?? self::GUEST;
    }
}
