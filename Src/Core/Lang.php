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
        /** @phpstan-ignore-next-line */
        $pathCache = STORAGE_CACHE . '/Lang';
        $path = Application::$ROOT_DIR . '/Lang';
        $lang = Application::$LANG;
        
        $pathPHP = "{$pathCache}/{$lang}_{$group}.php";
        $pathJSON = "{$path}/{$lang}/{$group}.json";

        $status = true;
        
        if (isset($this->data[$group])) {
            return $status;
        }
        
        if (!is_dir("{$path}/{$lang}")) {
            
            $lang   = $this->default;
            $status = false;
        }

        $fileNotExists = !file_exists($pathPHP);
        $fileModified = filemtime($pathPHP) < filemtime($pathJSON);
        if ($fileNotExists || $fileModified || Application::$DEBUG_MODE) {
            if (file_exists($pathJSON)) {
                
                /** @phpstan-ignore-next-line */
                $json = json_decode(file_get_contents($pathJSON), true);
                $this->data[$group] = $json;
                return $this->cacheJson($json, $lang, $group);
            } else {
                return false;
            }

        }
    
        $this->data[$group] = include $pathPHP;
        return true;

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

        return $this->data[$group][$name] ?? "{$group}_{$name}";
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
        /** @phpstan-ignore-next-line */
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

        if (!is_dir($path))
            mkdir($path, 0777, true);

        return file_put_contents($path . "/{$lang}_{$group}.php", $fileContent) !== false;
    }
}