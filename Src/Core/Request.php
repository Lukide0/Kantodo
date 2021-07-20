<?php 

namespace Kantodo\Core;

class Request
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    private $path = NULL;

    public function getPath()
    {

        if ($this->path !== NULL)
            return $this->path;

        $path = str_replace(Application::$URL_PATH, '',$_SERVER['REQUEST_URI']);

        $questionMarkPos = strpos($path, '?');

        if ($questionMarkPos === false)
            return $path;

        return $this->path = substr($path, 0, $questionMarkPos);

    }

    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getCookie(string $key) 
    {
        return  $_COOKIE[$key] ?? NULL;
    }

    public function getPostTokenCSRF()
    {
        if (isset($_POST['CSRF_TOKEN']))
            return $_POST['CSRF_TOKEN'];
        return "";
    }

    public function getBody()
    {
        $body = [
            "post" => [],
            "get" => []
        ];

        $body["get"] = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];

        if ($this->getMethod() == self::METHOD_POST) 
        {
            $body["post"] = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        }
        return $body;
    }
}



?>