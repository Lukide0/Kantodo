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
     * @var Router
     */
    public $router;

    public function __construct()
    {
        parent::__construct();

        self::$API          = $this;

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
