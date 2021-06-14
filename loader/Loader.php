<?php


class Loader 
{
    private $namespaceMap = array();
    private $classMap = array();
    private $missing = array();


    public function SetNamespace(string $alias, string $path)
    {

        if (isset($this->namespaceMap[$alias])) 
            return;
        
        $this->namespaceMap[$alias] = $path;
    }

    public function SetClass(string $alias, string $className)
    {

        if (isset($this->classMap[$alias])) 
            return;
        
        $this->classMap[$alias] = $className;
    }

    public function LoadClass(string $className) 
    {

        if (isset($this->missing[$className]))
            return false;
        $file =  $this->FindFile($className);

        if (!$file)
        {
            $this->missing[$className] = true;
            return true;
        }
        
        IncludeFile($file);
    }
    
    public function FindFile(string $className) 
    {

        $namespaces = explode("\\", $className);
        $class = array_pop($namespaces);


        $tmp = "";
        $match = null;
        $skip = 0;
        for ($i=0; $i < count($namespaces); $i++) { 
            $tmp .= $namespaces[$i] . "\\";

            if (isset($this->namespaceMap[$tmp])) 
            {   
                $skip = $i;
                $match = $this->namespaceMap[$tmp];
            }
        }

        unset($tmp);


        $file = "";
        if ($match != null) 
        {
            $file = $match .  implode( "/" ,array_slice($namespaces, $skip + 1));
        } else {
            $file = implode('/', $namespaces);
        }

        if (isset($this->classMap[$class])) 
        {
            $class = $this->classMap[$class];
        }

        if (strlen($file) == 0)
            return false;

        if ($file[strlen($file) - 1] != '/')
            $file .= '/';
        
        $file .= $class . ".php";


        if (!file_exists($file)) 
            return false;
        return $file;


    }

    public function Register() 
    {
        spl_autoload_register([$this, 'LoadClass']);
    }

}

function IncludeFile(string $file) 
{
    include $file;
}