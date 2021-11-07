<?php

require_once __DIR__ . '/Loader.php';
$autoloader = Autoloader::getLoader();

require_once dirname(__DIR__) . '/vendor/autoload.php';

return $autoloader;
