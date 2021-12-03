<?php

namespace Kantodo;

include "../Loader/autoload.php";


use Kantodo\Websocket\Server\Console;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;



class WsNotification implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        Console::memory(true);
    }
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        Console::log("NEW CLIENT");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        Console::warning($msg);
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        Console::log("DISCONNECT: {$conn->resourceId}");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        Console::error($e->getMessage());
        $conn->close();
    }
}


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WsNotification()
        )
    ),
    8443
);

$server->run();

