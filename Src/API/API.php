<?php 

namespace Kantodo\API;

use Kantodo\Core\BaseApplication;
use Kantodo\Core\Router;

class API extends BaseApplication {
    /**
     * Cesta k akcím
     *
     * @var string
     */
    public static $ACTIONS_PATH;

       /**
     * @var Router
     */
    public $router;

    public function __construct() {
        parent::__construct();

        self::$ACTIONS_PATH = self::$ROOT_DIR . '/API/Actions';
        $this->router   = new Router($this->request, $this->response);
    }

    /**
     * Začne API
     *
     * @return  void
     */
    public function run()
    {
        $this->router->resolve();
    }
}

?>