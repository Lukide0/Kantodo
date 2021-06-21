<?php 

namespace Kantodo\Core;


class Application
{
    const EVERYONE = 0;
    const GUEST = 1;
    const USER = 2;
    const TEAM_ADMIN = 3;
    const ADMIN = 4;

    public static $APP;
    public static $ROOT_DIR;
    public static $PAGES_DIR;
    public static $URL_PATH;
    public static $SCRIPT_URL;
    public static $STYLE_URL;

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

    private $eventListeners = array();
    private $userRole = self::EVERYONE;

    public function __construct() 
    {
        self::$APP = $this;

        $rootDir = dirname(dirname(__DIR__)); // this file dir

        self::$ROOT_DIR = dirname(
                            // src dir
                            dirname(
                                // core dir
                                __DIR__
                            )
                        );

        self::$URL_PATH = str_replace($_SERVER['DOCUMENT_ROOT'], '',(str_replace("\\",'/',self::$ROOT_DIR)));
        self::$PAGES_DIR = self::$ROOT_DIR . '/Pages';

        self::$SCRIPT_URL = self::$URL_PATH . '/Scripts/';
        self::$STYLE_URL = self::$URL_PATH . '/Styles/';
        
        $this->request = new Request();
        $this->response = new Response();
        $this->header = new HeaderHTML();
        $this->router = new Router($this->request, $this->response);
        $this->lang = new Lang();
    }

    public function Run()
    {
        $this->lang->Load();
        $this->router->Resolve();
    }

    public function On($eventName, $callback) 
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    public function Trigger($eventName) 
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    public static function GetRole() 
    {

        if (self::$APP->userRole != self::EVERYONE)
            return self::$APP->userRole;

        self::$APP->userRole = self::GUEST;

        if (empty($_SESSION) OR
            empty($_SESSION['userID']) OR
            empty($_SESSION['exp']))
        {
            return self::GUEST;
        }

        if ($_SESSION['exp'] <= time()) return self::GUEST;


        // DB user EXISTS
        self::$APP->userRole = self::USER;
        return self::USER;
    }

    public static function ConfigExits() 
    {
        return file_exists(self::$ROOT_DIR . '/config.php');
    }
}


?>