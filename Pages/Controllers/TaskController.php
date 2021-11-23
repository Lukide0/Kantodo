<?php

declare(strict_types = 1);

namespace Kantodo\Controllers;

use function Kantodo\Core\Functions\base64DecodeUrl;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Core\Validation\Data;
use Kantodo\Models\ColumnModel;

/**
 * Třída na práci s úkoly
 */
class TaskController extends AbstractController
{
    /**
     * Akce na vytvoření úkolu
     *
     * @param   array<mixed>  $params  parametry z url
     *
     * @return  void
     */
    public function createTask(array $params = [])
    {

        $body = Application::$APP->request->getBody();
        $post = $body[Request::METHOD_POST];

        $response = Application::$APP->response;

        $projID = base64DecodeUrl($params['projID']);

        $keys = [
            'projectUUID',
            'taskName',
            'taskDescription',
            'taskPriority',
            'taskEndDate',
        ];

        if (Data::isEmpty($post, $keys)) {
            $response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        // TODO: dodelat vytvareni

        // TODO: dodelat
        /*$wsClient = new Websocket('localhost', 8090);
    $wsClient->connect(Application::$URL_PATH . '/websockets');

    $wsClient->send($body[Request::METHOD_POST]['taskName']);*/

    }
}
