<?php

/**
 * Třída, která má za práci include podle namespace.
 *  
 */
class Loader
{
    private $namespaceMap = array();
    private $classMap     = array();
    private $missing      = array();

    /**
     * Nastavení aliasu namespace
     *
     * @param   string  $alias  alias
     * @param   string  $path   cesta
     *
     * @return  void          
     */
    public function setNamespace(string $alias, string $path)
    {

        if (isset($this->namespaceMap[$alias])) {
            return;
        }

        $this->namespaceMap[$alias] = $path;
    }

    /**
     * Natavení aliasu pro třídu
     *
     * @param   string  $alias      alias
     * @param   string  $className  název třídy s namespace
     *
     * @return  void              
     */
    public function setClass(string $alias, string $className)
    {

        if (isset($this->classMap[$alias])) {
            return;
        }

        $this->classMap[$alias] = $className;
    }

    public function loadClass(string $className)
    {

        if (isset($this->missing[$className])) {
            return false;
        }

        $file = $this->findFile($className);

        if (!$file) {
            $this->missing[$className] = true;
            return true;
        }

        IncludeFile($file);
    }

    /**
     * Najde cestu k souboru
     *
     * @param   string  $className  třída s namespace
     *
     * @return  string|false        Pokud soubor neexistuje v vrácen false
     */
    public function findFile(string $className)
    {
        // rozdělení:  "Neco1\Neco2\Trida" => ["Neco1", "Neco2", "Trida"]
        $namespaces = explode('\\', $className);
        $class      = array_pop($namespaces);

        $tmp   = '';
        $match = null;
        $skip  = 0;
        for ($i = 0, $size = count($namespaces); $i < $size; $i++) {

            // přidání cesty
            $tmp .= $namespaces[$i] . '\\';

            // zkontrolovaní jestli již je nastavená cesta
            if (isset($this->namespaceMap[$tmp])) {
                $skip  = $i;
                $match = $this->namespaceMap[$tmp];
            }
        }

        unset($tmp);

        $file = '';
        // Převedení namespace na cestu
        if ($match != null) {
            // array_slice odstraněni z cesty, již nastavenou např. Neco1\Neco3 => Neco3
            $file = $match . implode('/', array_slice($namespaces, $skip + 1));
        } else {
            $file = implode('/', $namespaces);
        }

        // Pokud již je nastavená třída
        if (isset($this->classMap[$class])) {
            $class = $this->classMap[$class];
        }

        if (strlen($file) == 0) {
            return false;
        }

        if ($file[strlen($file) - 1] != '/') {
            $file .= '/';
        }

        // Přidání jména třídy jako název souboru
        $file .= $class . '.php';

        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }
}

function includeFile(string $file)
{
    require $file;
}

class Autoloader
{
    public static function getLoader()
    {
        $loader = new Loader();

        $map = require __DIR__ . '/map_namespaces.php';

        foreach ($map as $alias => $path) {
            $loader->setNamespace($alias, $path);
        }

        $map = require __DIR__ . '/map_classes.php';

        foreach ($map as $alias => $className) {
            $loader->setClass($alias, $className);
        }

        $files = require __DIR__ . '/map_files.php';

        foreach ($files as $file) {
            includeFile($file);
        }

        $loader->register();

        return $loader;
    }
}
