<?php

namespace Kantodo\Core;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Database\Connection;

/**
 * Aplikace
 */
class Application
{
    // role
    const GUEST = 0;
    const USER  = 1;
    const ADMIN = 2;

    // error
    const ERROR_NOT_AUTHORIZED = 401; // 401
    const ERROR_NOT_FOUND      = 404; // 404

    /**
     * @var Application
     */
    public static $APP;
    /**
     * Cesta k aplikaci
     *
     * @var string
     */
    public static $ROOT_DIR;
    /**
     * Cesta ke stránkám
     *
     * @var string
     */
    public static $PAGES_DIR;
    /**
     * Cesta k migraci
     *
     * @var string
     */
    public static $MIGRATION_DIR;
    /**
     * Cesta k aplikaci v url
     *
     * @var string
     */
    public static $URL_PATH;
    /**
     * Cesta ke skriptům
     *
     * @var string
     */
    public static $SCRIPT_URL;
    /**
     * Cesta ke stylům
     *
     * @var string
     */
    public static $STYLE_URL;
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
     * Jazyk
     *
     * @var string
     */
    public static $LANG = 'en';

    /**
     * @var bool
     */
    public static $DEBUG_MODE = false;

    /**
     * @var bool
     */
    public static $CONFIG_LOADED = false;

    /**
     * @var bool
     */
    public static $INSTALLING = false;

    /**
     * @var Router
     */
    public $router;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Response
     */
    public $response;
    /**
     * @var HeaderHTML
     */
    public $header;
    /**
     * @var AbstractController
     */
    public $controller;
    /**
     * @var Lang
     */
    public $lang;
    /**
     * @var Session
     */
    public $session;

    /**
     * @var IAuth
     */
    private static $AUTH;
    /**
     * @var array<string,array<callable>>
     */
    private $eventListeners = [];
    /**
     * @var int
     */
    private $userRole = null;

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

        self::$URL_PATH      = str_replace($_SERVER['DOCUMENT_ROOT'], '', (str_replace('\\', '/', self::$ROOT_DIR)));
        self::$PAGES_DIR     = self::$ROOT_DIR . '/pages';
        self::$MIGRATION_DIR = self::$ROOT_DIR . '/migrations';

        self::$SCRIPT_URL = self::$URL_PATH . '/scripts/';
        self::$STYLE_URL  = self::$URL_PATH . '/styles/';
        self::$DATA_PATH  = self::$ROOT_DIR . '/data/';

        $this->request  = new Request();
        $this->response = new Response();
        $this->header   = new HeaderHTML();
        $this->session  = new Session();
        $this->router   = new Router($this->request, $this->response);
        $this->lang = new Lang();

        if ($this->configExits()) {
            $this->loadConfig();
            $this->lang->load();
        }
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

        // datable
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
     * Přepíše config.php
     *
     * @param   array<string,string>  $constants  konstanty
     *
     * @return  void
     */
    public static function overrideConfig(array $constants)
    {
        $content = '';

        foreach ($constants as $key => $value) {
            $content .= "define('{$key}', {$value});\n";
        }

        $content = "<?php\n{$content}\n?>";

        file_put_contents(self::$ROOT_DIR . '/config.php', $content);
    }

    /**
     * Přidá do configu konstanty
     *
     * @param   array<string,string>  $constants
     *
     * @return  void
     */
    public static function addToConfig(array $constants)
    {
        $content = file_get_contents(self::$ROOT_DIR . '/config.php') . "\n\n";

        foreach ($constants as $key => $value) {
            $content .= "define('{$key}', {$value});\n";
        }

        file_put_contents(self::$ROOT_DIR . '/config.php', $content);
    }
    /**
     * Začne aplikaci
     *
     * @return  void
     */
    public function run()
    {
        $this->loadConfig();
        $this->router->resolve();
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

    /**
     * Zkontroluje jestli existuje config.php
     *
     * @return  bool
     */
    public static function configExits()
    {
        return file_exists(self::$ROOT_DIR . '/config.php');
    }
}
