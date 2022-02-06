<?php

include "../../Loader/autoload.php";

use Kantodo\Auth\Auth;

$token = $argv[1];
var_dump(Auth::checkToken($token));
?>