<?php 

namespace Kantodo\Core;

class Response
{
    public function SetStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function SetLocation(string $location = '/') 
    {
        $url = Application::$URL_PATH . $location;

        header("location:$url");
    }
}


?>