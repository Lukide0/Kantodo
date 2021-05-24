<?php 
    include_once "WebSocket.php";

    $websoket = new WebSocket("localhost", 8090);
    $websoket->run();


?>