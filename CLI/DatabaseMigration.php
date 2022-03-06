<?php

namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Application;
use Kantodo\Core\Database\Migration\Runner;

include 'loader/autoload.php';

// jsou potřeba 3 argumenty
if ($argc < 3) {
    exit;
}

$APP = new Application();

// 1.0 => 1_0
$version = str_replace('.', '_', $argv[1]);
$actions = array_slice($argv, 2);

$execute       = false;
$outputFile    = false;
$loadSchema    = true;
$setCurrentVer = "";

for ($i = 0, $size = count($actions); $i < $size; $i++) {
    $action = $actions[$i];

    if ($action == '-o') {
        $outputFile = true;
    } else if ($action == '-e') {
        $execute = true;
    } else if ($action == '-n') {
        $loadSchema = false;
    } else if ($action == '-c') {
        $setCurrentVer = $actions[$i + 1];
        $i++;
    }
}

$r = new Runner($loadSchema, $setCurrentVer);

// Spustí migraci
$r->run($version, $execute, $outputFile, $setCurrentVer);
