<?php
declare(strict_types=1);

namespace Kantodo\API\Controllers;

use DateTime;
use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
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
            'task_proj',
            'task_comp',
            'task_priority'
        ];

        $empty = Data::notSet($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        $taskName = $body[Request::METHOD_POST]['task_name'];
        $taskDesc = $body[Request::METHOD_POST]['task_desc'];
        $taskCompleted = $body[Request::METHOD_POST]['task_comp'];
        $taskPriority = $body[Request::METHOD_POST]['task_priority'];
        $taskEndDate = $body[Request::METHOD_POST]['task_end_date'] ?? null;

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

        if (!DataType::wholeNumber($taskCompleted, 0, 1) || !DataType::wholeNumber($taskPriority, 0, 2)) 
        {
            $response->fail(['error' => t('bad_request')]);
            exit;
        }

        $taskPriority = (int)$taskPriority;
        $taskCompleted = (bool)$taskCompleted;

        if ($taskEndDate !== null) 
        {
            $date = new DateTime($taskEndDate);

            if ($date != false) 
            {
                $taskEndDate = $date;
            } else 
            {
                $response->fail(['error' => t('bad_request')]);
                exit;
            }
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

        $taskID = $taskModel->create($taskName, (int)$user['id'], (int)$details['id'], $taskDesc, $taskPriority, $taskEndDate, $taskCompleted);
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

            //if ($status === false)
            //    $response->error(t('can_not_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }


        // https://stackoverflow.com/a/28738208
        ob_start();

        $response->success([
            'task' => 
            [
                'id' => $taskID
            ]
            ],
            Response::STATUS_CODE_CREATED
        );
        

        $size = ob_get_length();

        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");

        ob_end_flush();
        @ob_flush();
        flush();

        $keyMaterial = API::getSymmetricKey();
        if ($keyMaterial === false)
            exit;
        
        $key = new SymmetricKey($keyMaterial);
        $paseto = (new Builder())
            ->setVersion(new Version4)
            ->setPurpose(Purpose::local())
            ->setKey($key)
            // nastavení dat
            ->setClaims([
                'user_id' => $user['id'],
                'task_id' => (string)$taskID,
                'project_uuid' => $projUUID    
            ])
            // nastavení vzniku
            ->setIssuedAt()
            // nastavení předmětu
            ->setSubject('ws_update')
            ->toString();
        $paseto = base64EncodeUrl($paseto);
        
        $client = \Ratchet\Client\connect('ws://127.0.0.1:8443')->then(function($conn) use ($paseto){
            $conn->send($paseto);
            $conn->close();
        });
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
        
        $projectId = 0;
        if (ProjectModel::hasPrivTo('viewTask', (int)$user['id'], $uuid, $projectId) === false)
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        
        $taskModel = new TaskModel();
        $tasks = $taskModel->get(['task_id' => 'id', 'name', 'description', 'priority', 'completed', 'end_date'], ['project_id' => $projectId, 'task_id' => ['>', $last]], $limit);
        
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
     * Akce na úpravu úkolu
     *
     * @return  void
     */
    public function update()
    {
        // TODO: tagy + frontend
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $user = Auth::getUser();

        $keys = [
            'task_id',
            'task_proj'
        ];

        $empty = Data::notSet($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }


        if ($user === null || empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }
        
        $taskID = $body[Request::METHOD_POST]['task_id'];
        $taskProj = $body[Request::METHOD_POST]['task_proj'];


        $uuid = base64DecodeUrl($taskProj);

        if ($uuid === false)
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }
    
        if (!DataType::wholeNumber($taskID, 1)) 
        {
            $response->error(t('task_id_is_not_valid', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }
        
        $taskData = [
            'name' => $body[Request::METHOD_POST]['task_name'] ?? NULL,
            'description' => $body[Request::METHOD_POST]['task_desc'] ?? NULL,
            'priority' => $body[Request::METHOD_POST]['task_priority'] ?? NULL,
            'completed' => $body[Request::METHOD_POST]['task_comp'] ?? NULL,
            'end_date' => $body[Request::METHOD_POST]['task_end_date'] ?? NULL
        ];
        

        foreach ($taskData as $key => $value) {
            if ($value === NULL) 
            {
                unset($taskData[$key]);
            }
        }

        if (count($taskData) == 0) 
        {
            $response->fail([]);
            exit;
        }



        $projectId = 0;
        if(ProjectModel::hasPrivTo('editTask', (int)$user['id'], $uuid, $projectId) !== true) 
        {
            
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        
        $taskModel = new TaskModel();
        
        $exists = $taskModel->getSingle(['task_id'], ['project_id' => $projectId, 'task_id' => $taskID]);
        
        
        if ($exists === false) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $status = $taskModel->update((int)$taskID, $taskData);

        if ($status === false) 
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        } else {
            $response->success(null,Response::STATUS_CODE_OK);
        }
        
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

        $empty = Data::notSet($body[Request::METHOD_POST], $keys);

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

        if (DataType::wholeNumber($taskID, 1)) 
        {
            $response->error(t('task_id_is_not_valid', 'api'), Response::STATUS_CODE_BAD_REQUEST);
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
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        } else {
            $response->success(null,Response::STATUS_CODE_OK);
        }

    }
}