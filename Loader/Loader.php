<?php

class Loader
{
    private $namespaceMap = array();
    private $classMap     = array();
    private $missing      = array();

    public function setNamespace(string $alias, string $path)
    {

        if (isset($this->namespaceMap[$alias])) {
            return;
        }

        $this->namespaceMap[$alias] = $path;
    }

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

    public function findFile(string $className)
    {

        $namespaces = explode('\\', $className);
        $class      = array_pop($namespaces);

        $tmp   = '';
        $match = null;
        $skip  = 0;
        for ($i = 0, $size = count($namespaces); $i < $size; $i++) {
            $tmp .= $namespaces[$i] . '\\';

            if (isset($this->namespaceMap[$tmp])) {
                $skip  = $i;
                $match = $this->namespaceMap[$tmp];
            }
        }

        unset($tmp);

        $file = '';
        if ($match != null) {
            $file = $match . implode('/', array_slice($namespaces, $skip + 1));
        } else {
            $file = implode('/', $namespaces);
        }

        if (isset($this->classMap[$class])) {
            $class = $this->classMap[$class];
        }

        if (strlen($file) == 0) {
            return false;
        }

        if ($file[strlen($file) - 1] != '/') {
            $file .= '/';
        }

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
