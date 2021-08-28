<?php

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Websocket\Client\Websocket;

/**
 * Třída na práci s úkoly
 */
class TaskController extends AbstractController
{
    /**
     * Akce na vytvoření úkoli
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function createTask(array $params = [])
    {
        
        $body = Application::$APP->request->getBody();
                
        $wsClient = new Websocket('localhost', 8090);
        $wsClient->connect(Application::$URL_PATH . '/websockets');

        $wsClient->send($body['post']['taskName']);

    }
}
