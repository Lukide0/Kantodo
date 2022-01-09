<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Auth\Auth;
use Kantodo\Core\Validation\Data;
use Kantodo\Core\Validation\DataType;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TagModel;
use Kantodo\Models\TaskModel;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\Purpose;

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
        $body = API::$API->request->getBody();
        $response = API::$API->response;

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
        $projUUID = base64DecodeUrl($body[Request::METHOD_POST]['task_proj']);
        $user = Auth::getUser();

        if ($projUUID === false) 
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if ($user === null || empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }



        $projModel = new ProjectModel();

        $details = $projModel->getBaseDetails((int)$user['id'], $projUUID);
        if ($details === false) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        $priv = $projModel->getPositionPriv($details['name']);

        if ($priv === false || !$priv['addTask']) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        
        
        $taskModel = new TaskModel();

        // TODO: priorita a konec
        $taskID = $taskModel->create($taskName, (int)$user['id'], (int)$details['id'], $taskDesc);
        if ($taskID === false) 
        {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }
        

        if (!empty($body[Request::METHOD_POST]['task_tags']) && is_array($body[Request::METHOD_POST]['task_tags'])) 
        {
            $tagModel = new TagModel();

            $tags = [];
            foreach ($body[Request::METHOD_POST]['task_tags'] as $tag) {
                $tagID = $tagModel->createInProject($tag, (int)$details['id']);
                
                if ($tagID === false) 
                {
                    $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
                } 
                else
                    $tags[] = $tagID;
            
            }

            
            $status = $tagModel->addTagsToTask($tags, $taskID);

            if ($status === false)
                $response->error(t('can_not_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }

        $keyMaterial = API::getSymmetricKey();

        if ($keyMaterial === false)
            exit;

        $response->success([
            'task' => 
            [
                'id' => base64EncodeUrl((string)$taskID),
                ]
            ],
            Response::STATUS_CODE_CREATED
        );
        /*
        $key = new SymmetricKey($keyMaterial);
        $paseto = (new Builder())
            ->setVersion(new Version4)
            ->setPurpose(Purpose::local())
            ->setKey($key)
            // nastavení dat
            ->setClaims([
                // TODO: user id
                'task_id' => (string)$taskID,
                'project_uuid' => $projUUID    
            ])
            // nastavení vzniku
            ->setIssuedAt()
            // nastavení předmětu
            ->setSubject('task_create')
            ->toString();
        $paseto = base64EncodeUrl($paseto);
        
        $client = \Ratchet\Client\connect('ws://127.0.0.1:8443')->then(function($conn) use ($paseto){
            $conn->send($paseto);
            $conn->close();
        });*/
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
        $response = API::$API->response;
        $user = Auth::getUser();
        $body = API::$API->request->getBody();
    
        $last = ($body[Request::METHOD_GET]['last'] ?? 0);

        if (DataType::wholeNumber($last)) 
        {
            $last = (int)$last;
        } else {
            $last = 0;
        }

        if ($user === null || empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        if (empty($params['projectUUID']))
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);

        $uuid = base64DecodeUrl($params['projectUUID']);

        if ($uuid === false)
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $projectModel = new ProjectModel();

        $projectId = $projectModel->projectMember((int)$user['id'], $uuid);

        if ($projectId === false)
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
        
        $taskModel = new TaskModel();
        $tasks = $taskModel->get(['task_id' => 'id', 'name', 'description', 'priority', 'completed', 'end_date'], ['project_id' => $projectId, 'task_id' => ['>', $last]], 10);
        
        if ($tasks === false) 
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;  
        }

        $tagModel = new TagModel();
        
        foreach ($tasks as &$task) {
            $taskID = (int)$task['id'];
            $task['tags'] = $tagModel->getTaskTags($taskID);
        }

        $response->success(['tasks' => $tasks]);      
    }

    /**
     * Akce na odstranění úkolu z projektu
     *
     * @return  void
     */
    public function remove()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $keys = [
            'task_id',
            'task_proj'
        ];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
        }

        $taskID = $body[Request::METHOD_POST]['task_id'];
        $projUUID = base64DecodeUrl($body[Request::METHOD_POST]['task_proj']);
        $user = Auth::getUser();

        if ($projUUID === false) 
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if ($user === null || empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        $projModel = new ProjectModel();
        $details = $projModel->getBaseDetails((int)$user['id'], $projUUID);
        if ($details === false) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        $priv = $projModel->getPositionPriv($details['name']);
        if ($priv === false || !$priv['removeTask']) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        
        
        $taskModel = new TaskModel();
        $status = $taskModel->delete((int)$taskID);
        if ($status === false) 
        {
            $response->error(t('can_not_remove', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }

        $response->success(null,Response::STATUS_CODE_OK);
    }
}