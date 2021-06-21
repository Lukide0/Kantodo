<?php


namespace Kantodo\Core;

class Lang
{
    private $json = [];
    private $path = Application::$ROOT_DIR . '/Lang/en.json';

    public function SetCode(string $code):bool
    {
        if (!file_exists(Application::$ROOT_DIR . "/Lang/$code.json")) 
            return false;
        
        $this->path = Application::$ROOT_DIR . "/Lang/$code.json";
        $this->_code = $code;
        return true;
    }

    public function Load() 
    {
        $text = file_get_contents($this->path);

        $json = @json_decode($text);

        if (!$json)
            return false;
        
        $this->json = $json;

        return true;
    }

    public function Get(string $name) 
    {
        return $this->json[$name] ?? "";

    }
}


?>