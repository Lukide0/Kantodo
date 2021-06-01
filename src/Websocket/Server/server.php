<?php

include_once "WebSocket.php";

$websoket = new \Kantodo\Websocket\Server\WebSocket("localhost", 8090, 20, "/Maturita%20-%20Kantodo/testing/");
$websoket->run();


?>