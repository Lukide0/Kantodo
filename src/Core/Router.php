<?php

declare(strict_types=1);

namespace Kantodo\Core;

use Kantodo\Core\Base\AbstractController;

/**
 * Router
 */
class Router
{
    /**
     * Dotaz
     *
     * @var Request
     */
    private $request;

    /**
     * Odpověd
     *
     * @var Response
     */
    private $response;
    /**
     * Cesty
     *
     * @var array<string,mixed>
     */
    protected $routes = [
        Request::METHOD_GET  => [],
        Request::METHOD_POST => [],
    ];

    /**
     * @var array<callable>
     */
    protected $errorHandlers = [];

    public function __construct(Request $r, Response $res)
    {
        $this->request  = $r;
        $this->response = $res;
    }

    /**
     * Přidá get cestu
     *
     * @param   string       $path      cesta
     * @param   mixed       $callback  callback
     * @param   int          $access    role
     * @param   bool         $strict    pouze uřčená role
     *
     * @return  void
     */
    public function get(string $path, $callback, int $access = BaseApplication::GUEST, bool $strict = false)
    {
        $this->routes[Request::METHOD_GET][$path] = ['callback' => $callback, 'access' => $access, 'strict' => $strict];
    }

    /**
     * Přidá post cestu
     *
     * @param   string       $path      cesta
     * @param   mixed       $callback  callback
     * @param   int          $access    role
     * @param   bool         $strict    pouze uřčená role
     *
     * @return  void
     */
    public function post(string $path, $callback, int $access = BaseApplication::GUEST, bool $strict = false)
    {
        $this->routes[Request::METHOD_POST][$path] = ['callback' => $callback, 'access' => $access, 'strict' => $strict];
    }

    /**
     * Přidá cestu
     *
     * @param   string      $method    metoda (Request::METHOD_POST, ...)
     * @param   string       $path      cesta
     * @param   mixed       $callback  callback
     * @param   int          $access    role
     * @param   bool         $strict    pouze uřčená role
     *
     * @return  void
     */
    public function addRoute(string $method, string $path, $callback, int $access = BaseApplication::GUEST, bool $strict = false)
    {
        $this->routes[$method][$path] = ['callback' => $callback, 'access' => $access, 'strict' => $strict];
    }

    /**
     * Vrátí registrovanou cestu, která se shoduje s dotazovanou cestou
     *
     * @param   string  $path    cesta
     * @param   string  $method  metoda
     *
     * @return  array<mixed>
     */
    public function match(string $path, string $method)
    {

        $routes = $this->routes[$method] ?? [];

        if (isset($routes[$path])) {
            return [$routes[$path], []];
        }

        $path           = ltrim($path, '/');
        $pathParts      = explode('/', $path);
        $pathPartsCount = count($pathParts);

        foreach ($routes as $route => $callback) {
            $route      = ltrim($route, '/');
            $routeParts = explode('/', $route);

            if (count($routeParts) != $pathPartsCount) {
                continue;
            }

            $tmp = [];
            for ($i = 0; $i < $pathPartsCount; $i++) {

                if (strlen($routeParts[$i]) > 0 && $routeParts[$i][0] == '{') {
                    $tmp[trim($routeParts[$i], "{}")] = $pathParts[$i];
                    continue;
                } elseif ($routeParts[$i] !== $pathParts[$i]) {
                    continue 2;
                }
            }

            return [$callback, $tmp];
        }
        return [false, []];
    }

    /**
     * Přidá manipulátor chybného kódu
     *
     * @param   int    $code      kód
     * @param   mixed  $callback  callback
     *
     * @return  void
     */
    final public function registerErrorCodeHandler(int $code, $callback)
    {
        $this->errorHandlers[$code] = $callback;
    }

    /**
     * Spustí manipulátor chybného kódu
     *
     * @param   int           $code    code
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    final public function handleErrorCode(int $code, array $params = [])
    {
        if (isset($this->errorHandlers[$code])) {
            call_user_func_array($this->errorHandlers[$code], $params);
            return;
        }

        $this->response->setStatusCode($code);
    }

    /**
     * Spustí router
     *
     * @return  void
     */
    public function resolve()
    {
        $path   = $this->request->getPath();
        $method = $this->request->getMethod();

        $this->runPath($path, $method);
    }

    /**
     * Spustí controller nebo callback
     *
     * @param   mixed           $callback   callback
     * @param   array<mixed>    $params     parametry
     *
     * @return  void
     */
    public function run($callback, array $params = [])
    {
        if (is_array($callback)) {
            $classMethod        = $callback[1];
            /**
             * @var AbstractController
             */
            $controller         = new $callback[0];
            $controller->action = $classMethod;

            try {
                $params = $controller->executeAllMiddlewares($params);
            } catch (\Throwable $th) {
                $this->handleErrorCode($th->getCode());
                exit;
            }

            /** @phpstan-ignore-next-line */
            call_user_func_array([$controller, $classMethod], $params);
            return;
        }

        call_user_func_array($callback, $params);
    }

    /**
     * Spustí cestu
     *
     * @param   string      $path    cesta
     * @param   string      $method  metoda
     *
     * @return  void
     *
     */
    public function runPath(string $path, string $method = Request::METHOD_GET)
    {
        [$callback, $params] = $this->match($path, $method);

        if ($callback === false) {
            $this->handleErrorCode(BaseApplication::ERROR_NOT_FOUND);
            return;
        }
        if (is_array($callback['callback'])) {
            $classMethod = $callback['callback'][1];

            /**
             * @var AbstractController
             */
            $controller = new $callback['callback'][0];

            $controller->action = $classMethod;
            $controller->access = $callback['access'];

            if (!$controller->hasAccess($callback['strict'])) {
                $this->handleErrorCode(
                    BaseApplication::ERROR_NOT_AUTHORIZED,
                    [
                        $callback['access'],
                        BaseApplication::getRole(),
                        $callback['strict']
                    ]
                );
                return;
            }

            try {
                $params = $controller->executeAllMiddlewares($params);
            } catch (\Throwable $th) {
                $this->handleErrorCode($th->getCode());
                exit;
            }
            /** @phpstan-ignore-next-line */
            call_user_func([$controller, $classMethod], $params);
            return;
        }
        call_user_func($callback['callback'], $this->request, $this->response, $params);
    }
}
