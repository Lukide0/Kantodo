<?php

declare(strict_types=1);

namespace Kantodo\Core;

use const Kantodo\Core\Functions\FILE_FLAG_CREATE_DIR;
use const Kantodo\Core\Functions\FILE_FLAG_OVERRIDE;

use function Kantodo\Core\Functions\filePutContentSafe;

/**
 * Překlad
 */
class Lang
{

    /**
     * @var array<string,array<string,string>>
     */
    private $data = [];

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

        $path      = Application::$ROOT_DIR . '/Lang';
        $lang      = Application::$LANG;

        if (defined("STORAGE_CACHE")) {
            $pathCache = STORAGE_CACHE . '/Lang';
            $pathPHP  = "{$pathCache}/{$lang}_{$group}.php";
        } else {
            $pathCache = null;
            $pathPHP = null;
        }

        $pathJSON = "{$path}/{$lang}/{$group}.json";

        $status = true;

        if (isset($this->data[$group])) {
            return $status;
        }

        if (!is_dir("{$path}/{$lang}")) {

            $lang   = $this->default;
            $status = false;
        }

        $filePHPExists = $pathPHP !== null && file_exists($pathPHP);
        $fileJSONExists = file_exists($pathJSON);

        if (!$fileJSONExists)
            return false;


        if (!$filePHPExists || Application::$DEBUG_MODE) {
            /** @phpstan-ignore-next-line */
            $json               = json_decode(file_get_contents($pathJSON), true);
            $this->data[$group] = $json;
            return $this->cacheJson($json, $lang, $group);
        }

        $fileModified  = filemtime($pathPHP) < filemtime($pathJSON);


        if ($fileModified) {
            /** @phpstan-ignore-next-line */
            $json               = json_decode(file_get_contents($pathJSON), true);
            $this->data[$group] = $json;
            return $this->cacheJson($json, $lang, $group);
        } else {
            $this->data[$group] = include $pathPHP;
            return true;
        }
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
        if (!isset($this->data[$group]))
            $this->load($group);

        return $this->data[$group][$name] ?? "{$group}:{$name}";
    }

    /**
     * Získá všechny slova ze skupiny
     *
     * @param   string  $group  skupina
     *
     * @return  array<string>          přeložené slovo
     */
    public function getAll(string $group)
    {
        if (!isset($this->data[$group]))
            return ($this->load($group)) ? $this->data[$group] : [];
        else
            return $this->data[$group];
    }

    /**
     * Vytvoří z JSON php soubor
     *
     * @param   array<mixed>   $json
     * @param   string  $lang   jazyk
     * @param   string  $group
     *
     * @return  bool          Podařilo se zapsat soubor
     */
    public function cacheJson(array $json, string $lang, string $group)
    {

        if (!defined("STORAGE_CACHE")) {
            return false;
        }

        $path = STORAGE_CACHE . "/Lang";

        $fileContent = "<?php\n\nreturn [\n";

        foreach ($json as $key => $value) {
            /**
             * @var string
             */
            $value = str_replace("'", "\'", $value);

            $fileContent .= "\t'{$key}' => '{$value}',\n";
        }

        $fileContent .= "];";

        return filePutContentSafe($path . "/{$lang}_{$group}.php", $fileContent, FILE_FLAG_OVERRIDE | FILE_FLAG_CREATE_DIR);
    }
}
