<?php

declare(strict_types=1);

namespace Kantodo\API;

use Kantodo\Core\Response;
use Kantodo\Core\BaseApplication;
use Kantodo\Core\Router;

class API extends BaseApplication
{

    /**
     * @var self
     */
    public static $API;

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

    public function __construct()
    {
        parent::__construct();

        self::$API          = $this;
        self::$ACTIONS_PATH = self::$ROOT_DIR . '/api/Actions';

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