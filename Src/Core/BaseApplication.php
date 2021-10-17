<?php

namespace Kantodo\Core;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Database\Connection;

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
    const ERROR_NOT_AUTHORIZED = 401; // 401
    const ERROR_NOT_FOUND      = 404; // 404

    /**
     * @var BaseApplication
     */
    public static $APP;

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
        self::$APP = $this;

        self::$ROOT_DIR = dirname(
            // src dir
            dirname(
                // core dir
                __DIR__
            )
        );

        self::$URL_PATH  = str_replace($_SERVER['DOCUMENT_ROOT'], '', (str_replace('\\', '/', self::$ROOT_DIR)));
        self::$DATA_PATH = self::$ROOT_DIR . '/data/';

        $this->request  = new Request();
        $this->response = new Response();
        $this->session  = new Session();
        $this->lang     = new Lang();

        if ($this->configExits()) {
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
        if (self::$APP->userRole !== null) {
            return self::$APP->userRole;
        }

        if (isset(self::$AUTH) && !self::$AUTH::isLogged()) {
            self::$APP->userRole = self::GUEST;
            return self::GUEST;
        }

        return self::$APP->userRole = self::$APP->session->get('user')['role'] ?? self::GUEST;
    }
}
