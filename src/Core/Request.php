<?php

namespace Kantodo\Core;

/**
 * Dotaz
 */
class Request
{
    const METHOD_GET  = 'get';
    const METHOD_POST = 'post';

    /**
     * Cesta na kterou bylo dotázáno
     *
     * @var string
     */
    private $path = null;

    /**
     * Získá cestu na kterou bylo dotázáno
     *
     * @return  string
     */
    public function getPath()
    {

        if ($this->path !== null) {
            return $this->path;
        }

        $path = str_replace(Application::$URL_PATH, '', $_SERVER['REQUEST_URI']);

        $questionMarkPos = strpos($path, '?');

        if ($questionMarkPos === false) {
            return $path;
        }

        return $this->path = substr($path, 0, $questionMarkPos);
    }

    /**
     * Získá jakou metodou bylo dotázáno
     *
     * @return  string
     */
    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Získá cookie podle klíče
     *
     * @param   string  $key        klíč
     * @param   mixed   $fallback   fallback
     *
     * @return  mixed               vrací fallback pokud klíč neexistuje
     */
    public function getCookie(string $key, $fallback = null)
    {
        return $_COOKIE[$key] ?? $fallback;
    }

    /**
     * Získá CSRF token
     *
     * @return  string
     */
    public function getPostTokenCSRF()
    {
        if (isset($_POST['CSRF_TOKEN'])) {
            return $_POST['CSRF_TOKEN'];
        }

        return '';
    }

    /**
     * Získá tělo dotazu
     *
     *
     * @return  array ['post', 'get']
     */
    public function getBody()
    {
        $body = [
            self::METHOD_POST => [],
            self::METHOD_GET  => [],
        ];

        $body[self::METHOD_GET] = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];

        if ($this->getMethod() == self::METHOD_POST) {
            $body[self::METHOD_POST] = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        }
        return $body;
    }
}
