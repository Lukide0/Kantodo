<?php 

namespace Kantodo\Core;

use DateTime;
use Kantodo\Core\Database\Connection;
use Kantodo\Models\UserModel;

class Application
{
    // roles
    const GUEST = 0;
    const USER = 1;
    const ADMIN = 2;

    // errors

    const ERROR_NOT_AUTHORIZED = 401; // 401
    const ERROR_NOT_FOUND = 404; // 404

    public static $APP;
    public static $ROOT_DIR;
    public static $PAGES_DIR;
    public static $MIGRATION_DIR;
    public static $URL_PATH;
    public static $SCRIPT_URL;
    public static $STYLE_URL;
    public static $DB_TABLE_PREFIX = '';
    public static $LANG = 'en';
    public static $DATA_PATH;
    public static $DEBUG_MODE = false;
    public static $CONFIG_LOADED = false;
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
    
    private $eventListeners = array();
    private $userRole = NULL;
    
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

        self::$URL_PATH = str_replace($_SERVER['DOCUMENT_ROOT'], '',(str_replace('\\','/',self::$ROOT_DIR)));
        self::$PAGES_DIR = self::$ROOT_DIR . '/Pages';
        self::$MIGRATION_DIR = self::$ROOT_DIR . '/Migrations';

        self::$SCRIPT_URL = self::$URL_PATH . '/Scripts/';
        self::$STYLE_URL = self::$URL_PATH . '/Styles/';
        self::$DATA_PATH = self::$ROOT_DIR . '/Data/';
        
        $this->request = new Request();
        $this->response = new Response();
        $this->header = new HeaderHTML();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response);
        $this->lang = new Lang();

        $this->lang->load();

        if ($this->configExits())
            $this->loadConfig();
    }

    public static function systemPathToUrl(string $path)
    {
        return str_replace(self::$ROOT_DIR, self::$URL_PATH, $path, 1);
    }

    public static function debugMode(bool $enable = true) 
    {
        // application
        self::$DEBUG_MODE = $enable;

        // datable
        if (self::$CONFIG_LOADED)
            Connection::debugMode();
        
    }

    public function loadConfig() 
    {
        if (self::$CONFIG_LOADED)
            return;

        include self::$ROOT_DIR . '/config.php';
        self::$CONFIG_LOADED = true;
        self::$DB_TABLE_PREFIX = DB_TABLE_PREFIX;
    }

    public static function overrideConfig(array $constants) 
    {
        $content = '';

        foreach ($constants as $key => $value) {
            $content .= "define('{$key}', {$value});\n";
        }

        $content = "<?php\n{$content}\n?>";

        file_put_contents(self::$ROOT_DIR . '/config.php', $content);

    }

    public function run()
    {
        $this->loadConfig();
        $this->router->resolve();
    }

    public function on($eventName, $callback) 
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    public function trigger($eventName) 
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    public static function getRole() 
    {
        if (self::$APP->userRole !== NULL)
            return self::$APP->userRole;
        
        if (!Auth::isLogged()) 
        {
            self::$APP->userRole = self::GUEST;
            return self::GUEST;
        }
    
        return self::$APP->userRole = self::$APP->session->get('role', Application::GUEST);
    }

    public static function configExits() 
    {
        return file_exists(self::$ROOT_DIR . '/config.php');
    }
}


?>