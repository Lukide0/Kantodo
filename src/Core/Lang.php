<?php


namespace Kantodo\Core;

class Lang
{
    private $json = [];
    private $default = 'en.json';

    public function Load(string $group = 'global'):bool
    {
        $path = Application::$ROOT_DIR . "/Lang/";

        $lang = Application::$LANG;
        $status = true;

        if (!file_exists($path . $lang . '.json')) 
        {
            $lang = $this->default;
            $status = false;
        }

        $text = file_get_contents($path . $lang . '.json');
        $json = @json_decode($text, true);

        if (!$json)
            return false;
        
        if (!isset($json[$group]))
            return false;
        
        $this->json = array_merge($json['global'],$json[$group]);

        return $status;
    }

    public function Get(string $name) 
    {
        return $this->json[$name] ?? "";

    }
}


?>