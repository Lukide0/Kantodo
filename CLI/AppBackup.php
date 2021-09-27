<?php

use Kantodo\Core\Application;
use Kantodo\Update\Backup;

include "Loader/autoload.php";


$APP = new Application();

$backup = new Backup();
$backup->createZip();



?>