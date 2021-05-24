<?php 

class Client 
{
    public $handshake = false;
    public $socket;
    //public $sockets = array();
    public $teamsId = array();

    public function __construct($socket, $teamsId = array()) {
        $this->socket = $socket;
        $this->teamsId =$teamsId;
    }
}


?>