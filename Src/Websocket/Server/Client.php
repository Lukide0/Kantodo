<?php

namespace Kantodo\Websocket\Server;

class Client
{
    public $handshake = false;
    public $socket;
    //public $sockets = array();
    public $projectsId = array();

    public function __construct($socket, $projectsId = array())
    {
        $this->socket  = $socket;
        $this->projectsId = $projectsId;
    }
}
