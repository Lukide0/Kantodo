<?php


namespace Kantodo\Core;

class Lang
{
    private $data = [];
    private $default = "en";

    public function load(string $group = 'global'):bool
    {
        $path = Application::$ROOT_DIR . "/Lang";
        $lang = Application::$LANG;

        $status = true;

        if (!file_exists("{$path}/{$lang}")) 
        {
            $lang = $this->default;
            $status = false;
        }

        if (!file_exists("{$path}/{$lang}/{$group}.php")) 
        {
            $group = 'global';
            $status = false;
        }

        if (isset($this->data[$group]))
            return $status;


        $this->data[$group] = include "{$path}/{$lang}/{$group}.php";

        return $status;

    }

    public function get(string $name, string $group = "global") 
    {
        return $this->data[$group][$name] ?? "";
    }
}


?>