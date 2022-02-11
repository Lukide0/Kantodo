<?php

declare(strict_types=1);

namespace Kantodo;


include dirname(__FILE__) ."/../Loader/autoload.php";

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\BaseApplication;
use Kantodo\Websocket\Console;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Rules\Subject;
use ParagonIE\Paseto\Rules\ValidAt;
use Psr\Http\Message\RequestInterface;
use Ratchet\App;
use Ratchet\ComponentInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\CloseResponseTrait;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\Http\HttpServerInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\WebSocket\WsServerInterface;
use SplObjectStorage;


class ProjectChannel 
{
    /**
     * @var SplObjectStorage<ConnectionInterface,mixed>
     */
    public $connections;

    public function __construct() {
        $this->connections = new SplObjectStorage();
    }

    public function add(ConnectionInterface $con) : void
    {
        $this->connections->attach($con);
    }

    public function remove(ConnectionInterface $con) : bool
    {
        $this->connections->detach($con);

        if ($this->connections->count() == 0)
            return true;
        return false;
    }

    public function sendAll(string $data) : void
    {
        foreach ($this->connections as $con) {
            $con->send($data);
        }
    }

    public function sendAllExcept(string $data, ConnectionInterface $sender) : void
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


class WsNotification implements MessageComponentInterface, WsServerInterface
{
    use CloseResponseTrait;

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
        // ConnectionInterface neobsahuje httpRequest, ale WsServer ho nastavuje
        
        $request = $conn->httpRequest;
        
        $header = $request->getHeader("Sec-Websocket-Protocol");
        if (count($header) == 0) 
        {
            $this->close($conn, 401);
            return;
        }
        // získání PASETO => "access_token, TOKEN"
        // strlen("access_token") + 2 == "access_token" + ',' + ' '
        $pasetoRaw = substr($header[0], strlen("access_token") + 2);
        if ($pasetoRaw == false || !Auth::checkToken($pasetoRaw)) 
        {
            $this->close($conn, 401);
            return;
        }

        $this->connections->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) : void
    {
        $decodedMSG = json_decode($msg, true);

        if (   $decodedMSG == NULL 
            || $decodedMSG == false 
            || !isset($decodedMSG['action']) 
            || !isset($decodedMSG['value'])
        )
            return;
        
        $action = $decodedMSG['action'];
        $value = $decodedMSG['value'];

        $from->send(count($this->channels));

        switch($action) 
        {
        case 'join':
            $this->joinChannel($from, $value);
            break;
        case 'leave':
            $this->leaveChannel($from, $value);
            break;
        default:
            return;
        }

    

        // TODO: types => subscribe, msg, unsubscribe,
        Console::warning($action);
    }

    private function joinChannel(ConnectionInterface $con, string $channelId)
    {
        if (isset($this->channels[$channelId])) 
        {
            $this->channels[$channelId]->add($con);
        }
        else 
        {
            $this->channels[$channelId] = new ProjectChannel();
            $this->channels[$channelId]->add($con);
        }
    }

    private function leaveChannel(ConnectionInterface $con, string $channelId) 
    {
        if (isset($this->channels[$channelId])) 
        {
            // zjistí jestli je prázdný kanál
            $removeChannel = $this->channels[$channelId]->remove($con);

            if ($removeChannel) 
            {
                Console::log("Closing channel: " . $channelId);
                Console::memory();
                unset($this->channels[$channelId]);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) : void {
        Console::memory();
        foreach ($this->channels as $channelId => $channel) {
            if ($channel->remove($conn)) 
            {
                unset($this->channels[$channelId]);
            }
        }

        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) : void {
        Console::error($e->getMessage());
        $conn->close();
    }

    final public function getSubProtocols() : array 
    {
        // Pokud chceme použít Sec-Websocket-Protocol, tak musíme implementovat WsServerInterface. Viz. poznámka WsServer konstruktor
        return ['access_token'];
    }
}

$app = new BaseApplication();

Console::log("Starting Websocket server");

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WsNotification()
        )
    ),
    8443
);

$server->run();
