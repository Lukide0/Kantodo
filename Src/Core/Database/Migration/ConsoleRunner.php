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

$r = new Runner();


$version = str_replace('.', '_', $argv[1]);
$actions = array_slice($argv,2);


$mode = $r->compareVersions($r->getCurrentVersion(), $version);

$execute = false;
$outputFile = false;

foreach ($actions as $action) {
    if ($action == '-o')
        $outputFile = true;
    
    elseif ($action == '-e')
        $execute = true;
}


$r->run($version, $execute, $outputFile);


?>