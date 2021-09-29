<?php

namespace Kantodo\Core;

/**
 * Překlad
 */
class Lang
{

    /**
     * @var array<string,array<string,string>>
     */
    private $data    = [];

    /**
     * @var string
     */
    private $default = 'en';

    /**
     * Načte překlad
     *
     * @param   string  $group  skupina
     *
     * @return  bool            status
     */
    public function load(string $group = 'global')
    {
        $path = Application::$ROOT_DIR . '/Lang';
        $lang = Application::$LANG;

        $status = true;

        if (!file_exists("{$path}/{$lang}")) {
            $lang   = $this->default;
            $status = false;
        }

        if (!file_exists("{$path}/{$lang}/{$group}.php")) {
            $group  = 'global';
            $status = false;
        }

        if (isset($this->data[$group])) {
            return $status;
        }

        $this->data[$group] = include "{$path}/{$lang}/{$group}.php";

        return $status;

    }

    /**
     * Získá slovo z načtených
     *
     * @param   string  $name   slovo
     * @param   string  $group  skupina
     *
     * @return  string          přeložené slovo
     */
    public function get(string $name, string $group = 'global')
    {
        return $this->data[$group][$name] ?? '';
    }
}
