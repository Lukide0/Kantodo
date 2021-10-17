<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\API\Response;
use Kantodo\Models\ProjectModel;

use function Kantodo\Core\Functions\t;

class ProjectController extends AbstractController
{
    public function create()
    {
        $body = API::$APP->request->getBody();
        $response = API::$APP->response;
        $session = API::$APP->session;

        if (empty($body[Request::METHOD_POST]['name'])) 
        {
            $response->fail(['name' => t('empty', 'api')]);
        }

        $projectName = $body[Request::METHOD_POST]['name'];
        $user = $session->get('user');

        if (empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
        }

        $projModel = new ProjectModel();

        $status = $projModel->create($user['id'], $projectName);
        if ($status === false) 
        {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }

        $response->success(['project' => [ 'uuid' => $status['uuid']]], Response::STATUS_CODE_CREATED);
    }
}
