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

    public function sendToUser(string $id, string $data) : void 
    {
        foreach($this->connections as $con) 
        {
            if ($con->id == $id)
                $con->send($data);
        }
    }

    public function sendAllExcept(string $data, ConnectionInterface $sender) : void
    {
        foreach ($this->connections as $con) {
            if ($sender == $con)
            {
                continue;
            }
            $con->send($data);
        }
    }

    public function removeSelf(string $project) : void
    {
        $this->sendAll(json_encode(['action' => 'project_remove', 'project' => $project]));
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
        
        // ConnectionInterface neobsahuje httpRequest, ale je nastavené v WsServer
        $request = $conn->httpRequest;
        
        $header = $request->getHeader("Sec-Websocket-Protocol");
        if (count($header) == 0) 
        {
            $this->close($conn, 401);
            return;
        }
        // získání PASETO => "access_token,TOKEN"
        $pasetoRaw = substr($header[0], strlen("access_token") + 1);
        if ($pasetoRaw == false) 
        {
            $this->close($conn, 401);
            return;
        }

        // občas může být za ',' mezera
        $pasetoRaw = trim($pasetoRaw);

        if (!Auth::checkToken($pasetoRaw))
        {
            $this->close($conn, 401);
            return;
        }

        // ID uživatele z PASETO
        $conn->id = Auth::$PASETO->get('id');
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

        switch($action) 
        {
        case 'join':
            $this->joinChannel($from, $value);
            break;
        case 'leave':
            $this->leaveChannel($from, $value);
            break;
        case 'task_create':
        case 'task_remove':
        case 'task_update':
        {
            if (!isset($decodedMSG['project']))
                break;
            
            $project = $decodedMSG['project'];
            $message = json_encode($decodedMSG);

            if (!isset($this->channels[$project]) || $message === false)
                break;
            
            $this->channels[$project]->sendAllExcept($message, $from);
            $from->close();

            $this->channels[$project]->remove($from);
            $this->connections->detach($from);

            break;
        }
        case 'project_remove':
        {
            if (!isset($decodedMSG['project']))
                break;
            
            $project = $decodedMSG['project'];
            if (!isset($this->channels[$project]))
                break;

            $this->channels[$project]->removeSelf($project);
            unset($this->channels[$project]);

            break;
        }
        case 'project_user_change':
        {
            if (!isset($decodedMSG['project']))
                break;
            
            $project = $decodedMSG['project'];
            if (!isset($this->channels[$project]))
                break;

            $this->channels[$project]->sendToUser($from->id, json_encode(['action' => 'user_change']));
            break;
        }
        case 'project_user_remove':
        {
            if (!isset($decodedMSG['project']))
                break;
            
            $project = $decodedMSG['project'];
            if (!isset($this->channels[$project]))
                break;

            $this->channels[$project]->sendToUser($from->id, json_encode(['action' => 'user_remove', 'project' => $project]));
            break;
        }
        default:
            return;
        }
    }

    /**
     * Připojí uživatele do kanálu projektu
     *
     * @param   ConnectionInterface  $con        uživatel
     * @param   string               $channelId  uuid projektu
     *
     * @return  void                           
     */
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

    /**
     * Odstraní uživatele z kanálu projektu
     *
     * @param   ConnectionInterface  $con        uživatel
     * @param   string               $channelId  uuid projektu
     *
     * @return  void                           
     */
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

    /**
     * Sub protokoly
     *
     * @return  array<string>
     */
    final public function getSubProtocols() : array 
    {
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

