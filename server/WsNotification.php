<?php

declare(strict_types=1);

namespace Kantodo;


include dirname(__FILE__) ."/../Loader/autoload.php";

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Websocket\Console;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Rules\Subject;
use ParagonIE\Paseto\Rules\ValidAt;
use Psr\Http\Message\RequestInterface;
use Ratchet\App;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\CloseResponseTrait;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;


$parserUpdate = Parser::getLocal(new SymmetricKey(Application::getSymmetricKey()))
        ->addRule(new ValidAt)
        ->addRule(new Subject("ws_update"));

class Client
{
    /**
     * @var array<ConnectionInterface>
     */
    public $connections = [];
    public $projects = [];
    public $userDetails = [];
    public $isAuth = false;

    public function __construct($con) {
        $this->connections[] = $con;
    }

    public function isSender(ConnectionInterface $con) 
    {
        return in_array($con, $this->connections);
    }

    public function openConnectionsCount() 
    {
        return count($this->connections);
    }

    public function closeConnection(ConnectionInterface $con)
    {
        if (($key = array_search($con, $this->connections)) !== false) 
        {
            $this->connections[$key]->close();
            unset($this->connections[$key]);
        }
    }

    public function send(string $message)
    {
        foreach ($this->connections as $value) {
            $value->send($message);
        }
    }
}


class AuthWs extends WsServer 
{
    use CloseResponseTrait;

    final public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        $paseto = Auth::getPasetoTokenFromRequest();


        if ($paseto == false || !Auth::checkToken($paseto)) 
        {
            return $this->close($conn, 401);
        }

        parent::onOpen($conn, $request);
    }
}


class WsNotification implements MessageComponentInterface
{
    /**
     * @var array<Client>
     */
    protected $clients;

    public function __construct() {
        $this->clients = [];
        Console::memory(true);
    }
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        // TODO: pridavat do clienta
        $this->clients[] = new Client($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        $findSender = false;
        foreach ($this->clients as $client) {
            if (!$findSender && $client->isSender($from))
                continue;
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        foreach ($this->clients as $key => $client) {
            if ($client->closeConnection($conn)) 
            {
                if ($client->openConnectionsCount() == 0)
                    unset($this->clients[$key]);
                
                break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        Console::error($e->getMessage());
        $conn->close();
    }
}

Console::log("Starting Websocket server");

$server = IoServer::factory(
    new HttpServer(
        new AuthWs(
            new WsNotification()
        )
    ),
    8443
);

$server->run();

