<?php

namespace Kantodo\Websocket\Client;


class Websocket
{
    private string $url;
    private int $port;

    public function __construct(string $url, int $port = 80) {
        $this->url = $url;
        $this->port = $port;
    }

    public function Connect()
    {
        # code...
    }
}






?>