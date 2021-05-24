<?php 

class Autoloader
{
    public static function GetLoader() 
    {
        require __DIR__ . '/Loader.php';

        $loader = new Loader();

        $map = require __DIR__ . '/map_namespaces.php';

        foreach ($map as $alias => $path) {
            $loader->SetNamespace($alias, $path);
        }

        $map = require __DIR__ . '/map_classes.php';
        
        foreach ($map as $alias => $className) {
            $loader->SetClass($alias, $className);
        }


        $loader->Register();

        return $loader;
    }
}

