<?php


namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Application;
use Kantodo\Core\Database\Migration\Runner;

include 'Loader/autoload.php';


if ($argc < 3) 
{
    exit;
}

$APP = new Application();

$version = str_replace('.', '_', $argv[1]);
$actions = array_slice($argv,2);


$execute = false;
$outputFile = false;
$loadSchema = true;
$setCurrentVer = "";

for ($i=0; $i < count($actions); $i++) { 
    $action = $actions[$i];

    if ($action == '-o')
        $outputFile = true;
    
    elseif ($action == '-e')
        $execute = true;
    
    elseif ($action == '-n')
        $loadSchema = false;

    elseif ($action == '-c') 
    {
        $setCurrentVer = $actions[$i + 1];
        $i++;
    }
}


$r = new Runner($loadSchema, $setCurrentVer);

$r->run($version, $execute, $outputFile, $setCurrentVer);


?>