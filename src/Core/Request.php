<?php 

namespace Kantodo\Core;

class Request
{
    public function GetPath()
    {


        $path = str_replace(Application::$URL_PATH, '',$_SERVER['REQUEST_URI']);

        $questionMarkPos = strpos($path, '?');

        if ($questionMarkPos === false)
            return $path;

        return substr($path, 0, $questionMarkPos);

    }

    public function GetMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function GetBody()
    {
        $body = array();

        if ($this->GetMethod() == 'get') 
        {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->GetMethod() == 'post') 
        {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }
}



?>