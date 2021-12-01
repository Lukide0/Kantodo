<?php

use Kantodo\Core\Application;
use Kantodo\Websocket\Server\WebSocket;

ini_set("html_errors", 0);

include "../loader/autoload.php";

$app = new Application();

define('WS_SECURE', false);
define('WS_PORT', 8443);

if (WS_SECURE) {
    $uri = 'tls://localhost';
} else {
    $uri = 'tcp://localhost';
}

// $g = stream_context_create (array("ssl" => array("capture_peer_cert" => true, "verify_peer_name" => false, "verify_peer" => false)));
// $r = stream_socket_client("ssl://localhost:443", $errno, $errstr, 30,
//     STREAM_CLIENT_CONNECT, $g);
// $cont = stream_context_get_params($r);
// $cert = openssl_x509_parse($cont['options']['ssl']['peer_certificate']);

// var_dump($cert);
// exit;

$server = new WebSocket($uri, WS_PORT, Application::$URL_PATH . '/websockets');

$server->onMessage = "message";

function message($data)
{
    echo $data['message'] . "\n";
}
$server->run();
