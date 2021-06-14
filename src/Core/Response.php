<?php 

namespace Kantodo\Core;

class Response
{
    public function SetStatusCode(int $code)
    {
        http_response_code($code);
    }
}


?>