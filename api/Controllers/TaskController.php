<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\API\Response;
use Kantodo\Core\Validation\Data;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TaskModel;

use function Kantodo\Core\Functions\base64DecodeUrl;
use function Kantodo\Core\Functions\base64EncodeUrl;
use function Kantodo\Core\Functions\t;

class TaskController extends AbstractController
{
    /**
     * Akce na vytvoření úkolu
     *
     * @return  void
     */
    public function create()
    {
        $body = API::$APP->request->getBody();
        $response = API::$APP->response;
        $session = API::$APP->session;

        $keys = [
            'task_name',
            'task_desc',
            'task_proj'
        ];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
        }

        $taskName = $body[Request::METHOD_POST]['task_name'];
        $taskDesc = $body[Request::METHOD_POST]['task_desc'];
        $projUUID = $body[Request::METHOD_POST]['task_proj'];
        $user = $session->get('user');

        if (empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
        }



        $projModel = new ProjectModel();

        $details = $projModel->getBaseDetails($user['id'], $projUUID);
        if ($details === false) 
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            return;
        }
        $priv = $projModel->getPositionPriv($details['name']);

        if ($priv === false || !$priv['addTask']) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
        }
        
        
        $taskModel = new TaskModel();

        // TODO: priorita, milnik a konec
        $taskID = $taskModel->create($taskName, $user['id'], (int)$details['id'], $taskDesc);
        
        if ($taskID === false) 
        {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            return;
        }

        $response->success([
            'task' => 
                [
                    'id' => base64EncodeUrl((string)$taskID),
                ]
            ],
            Response::STATUS_CODE_CREATED
        );
    }

    /**
     * Akce na získání úkolů v projektu
     *
     * @param   array<string>  $params  parametry
     *
     * @return  void
     */
    public function get(array $params = [])
    {
        $limit = 10;
        $response = API::$APP->response;
        $session = API::$APP->session;
        $user = $session->get('user');
        $body = API::$APP->request->getBody();
    
        $offset = ($body[Request::METHOD_GET]['page'] ?? 0) * $limit;


        if (empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
        }

        if (empty($params['projectUUID']))
            $response->error(t('project uuid missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);

        $uuid = base64DecodeUrl($params['projectUUID']);
        $projectModel = new ProjectModel();

        $projectId = $projectModel->projectMember((int)$user['id'], $uuid);

        if (!$projectId)
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
        
        $taskModel = new TaskModel();
        $tasks = $taskModel->get(['*'], ['project_id' => $projectId], $limit, $offset);
        
        $response->success(['tasks' => $tasks]);      
    }
}
