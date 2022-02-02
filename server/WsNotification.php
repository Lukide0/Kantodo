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
use SplObjectStorage;

$symKey = Application::getSymmetricKey();

if ($symKey === false) 
{
    Console::error("Can't open file with sym. key");
    exit;
}

$parserUpdate = Parser::getLocal(new SymmetricKey($symKey))
        ->addRule(new ValidAt)
        ->addRule(new Subject("ws_update"));

class ProjectChannel 
{
    /**
     * @var SplObjectStorage<ConnectionInterface,mixed>
     */
    public $connections;

    public function __construct() {
        $this->connections = new SplObjectStorage();
    }

    public function add(ConnectionInterface &$con) : void
    {
        $this->connections->attach($con);
    }

    public function remove(ConnectionInterface &$con) : void
    {
        $this->connections->detach($con);
    }

    public function sendAll(string $data) : void
    {
        foreach ($this->connections as $con) {
            $con->send($data);
        }
    }

    public function sendAllExcept(string $data, ConnectionInterface &$sender) : void
    {
        $foundSender = false;
        foreach ($this->connections as $con) {
            if ($foundSender || $sender == $con) 
            {
                $foundSender = true;
                continue;
            }
            $con->send($data);
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
     * @var SplObjectStorage<ConnectionInterface,mixed>
    */
    private $connections;
    /**
     * @var array<string,ProjectChannel>
    */
    private $channels = [];

    public function __construct() {
        $this->connections = new SplObjectStorage();
        Console::memory();
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) : void {


        $this->connections->attach($conn);

        // TODO: pridat do channel
    }

    public function onMessage(ConnectionInterface $from, $msg) : void {

        // TODO: send to channel
        Console::log($msg);
    }

    public function onClose(ConnectionInterface $conn) : void {
        foreach ($this->connections as $client) {
            $client->close();
        }

        foreach ($this->channels as $channel) {
            $channel->remove($conn);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) : void {
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

