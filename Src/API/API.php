<?php

namespace Kantodo\API;

use Kantodo\API\Response;
use Kantodo\Core\BaseApplication;
use Kantodo\Core\Router;

class API extends BaseApplication
{

    /**
     * @var self
     */
    public static $APP;

    /**
     * Cesta k akcím
     *
     * @var string
     */
    public static $ACTIONS_PATH;

    /**
     * Max počet požadavků za 1min
     *
     * @var int
     */
    public static $MAX_REQUEST_COUNT_PER_MIN = 20;

    /**
     * @var Router
     */
    public $router;

    /**
     * @var Response
     */
    public $response;

    public function __construct()
    {
        parent::__construct();

        self::$APP          = $this;
        self::$ACTIONS_PATH = self::$ROOT_DIR . '/API/Actions';

        $this->response = new Response();
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
