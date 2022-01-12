<?php

declare(strict_types=1);

namespace Kantodo;

include "../Loader/autoload.php";


use Kantodo\Websocket\Console;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class Client
{
    /**
     * @var ConnectionInterface
     */
    public $con;
    public $projects = [];
    public $token;
    public $isAuth = false;

    public function __construct($con) {
        $this->con = $con;
    }

    public function isSender(ConnectionInterface &$con) 
    {
        return ($this->con == $con);
    }
}



class WsNotification implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        Console::memory(true);
    }
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach(new Client($conn));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $findSender = false;
        Console::warning("MSG");
        foreach ($this->clients as &$client) {
            if (!$findSender && $client->isSender($from))
                continue;
            $client->con->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        Console::memory();
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

