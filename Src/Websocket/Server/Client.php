<?php

declare(strict_types = 1);

namespace Kantodo\Websocket\Server;

class Client
{
    /**
     * @var bool
     */
    public $handshake = false;

    /**
     * @var resource
     */
    public $socket;
    //public $sockets = array();

    /**
     * Konstruktor
     *
     * @param   resource  $socket
     *
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
    }
}
