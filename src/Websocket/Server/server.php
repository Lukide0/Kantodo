<?php

include_once "WebSocket.php";

$websoket = new \Kantodo\Websocket\Server\WebSocket("localhost", 8090);
$websoket->run();


?>