<?php 

namespace Kantodo\Core;

class Application
{
    public static $APP;
    public static $ROOT_DIR;
    public static $PAGES_DIR;
    public static $URL_PATH;
    public static $SCRIPT_URL;
    public static $STYLE_URL;

    public $router;
    public $request;
    public $response;
    public $header;


    private $eventListeners = array();

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
    }

    public function Run()
    {
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
}


?>