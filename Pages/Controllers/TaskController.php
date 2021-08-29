<?php

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
     * @param   array  $params  parametry z url
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
            'column',
            'taskName',
            'taskDescription',
            'taskPriority',
            'taskEndDate',
        ];

        if (Data::isEmpty($post, $keys)) {
            $response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $columnID = base64DecodeUrl($post['column']);

        // TODO index, milestone

        $columnModel = new ColumnModel();

        $column = $columnModel->getSingle(['max_task_count' => 'max'], ['column_id' => $columnID, 'project_id' => $projID]);

        if ($column === false) {
            $response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if ($column['max'] !== null) {
            $countTasks = $columnModel->getCountOfTasks($columnID);

            if ($countTasks === false) {
                $response->setStatusCode(Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
                exit;
            }

            if ($column['max'] <= $countTasks) {
                $response->addResponseError('exceeded max task count of column');
                $response->outputResponse();
                exit;
            }
        }

        // TODO
        /*$wsClient = new Websocket('localhost', 8090);
    $wsClient->connect(Application::$URL_PATH . '/websockets');

    $wsClient->send($body[Request::METHOD_POST]['taskName']);*/

    }
}
