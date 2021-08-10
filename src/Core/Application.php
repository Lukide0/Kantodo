<?php

namespace Kantodo\Core;

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

    public static $DEBUG_MODE    = false;
    public static $CONFIG_LOADED = false;
    public static $INSTALLING    = false;

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
     * @var Controller
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

    private $eventListeners = [];
    private $userRole       = null;

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
        $this->lang     = new Lang();

        $this->lang->load();

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
        self::$CONFIG_LOADED   = true;
        self::$DB_TABLE_PREFIX = DB_TABLE_PREFIX;
    }

    /**
     * Přepíše config.php
     *
     * @param   array  $constants  konstanty
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

        if (!Auth::isLogged()) {
            self::$APP->userRole = self::GUEST;
            return self::GUEST;
        }

        return self::$APP->userRole = self::$APP->session->get('user')['role'];
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
