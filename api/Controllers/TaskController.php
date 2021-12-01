<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\API\Response;
use Kantodo\Auth\Auth;
use Kantodo\Core\BaseApplication;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Validation\Data;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TagModel;
use Kantodo\Models\TaskModel;
use Kantodo\Websocket\Client\Websocket;
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
        $session = API::$API->session;

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
        $taskID = 1;//$taskModel->create($taskName, $user['id'], (int)$details['id'], $taskDesc);
        /* if ($taskID === false) 
        {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            return;
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
                $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        } */

        $keyMaterial = API::getSymmetricKey();

        if ($keyMaterial === false)
            return null;

        $key = new SymmetricKey($keyMaterial);
        $paseto = (new Builder())
            ->setVersion(new Version4)
            ->setPurpose(Purpose::local())
            ->setKey($key)
            // nastavení dat
            ->setClaims([
                'task_id' => $taskID,
                'project_uuid' => $projUUID                
            ])
            // nastavení vzniku
            ->setIssuedAt()
            // nastavení předmětu
            ->setSubject('task_create')
            ->toString();
        $paseto = base64EncodeUrl($paseto);
        $client = new Websocket('tcp://localhost', 8443);
        if ($client->connect("/")) 
        {

            $client->send(Auth::$PASETO_RAW);
            $client->timeout(50);
            $client->send("TODO CREATE");
            $client->timeout(50);
            $client->disconnect();
        }
        $response->error("SEND", Response::STATUS_CODE_BAD_REQUEST);

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
        $response = API::$API->response;
        $session = API::$API->session;
        $user = $session->get('user');
        $body = API::$API->request->getBody();
    
        $offset = ($body[Request::METHOD_GET]['page'] ?? 0) * $limit;


        if (empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
        }

        if (empty($params['projectUUID']))
            $response->error(t('project uuid missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);

        $uuid = base64DecodeUrl($params['projectUUID']);

        if ($uuid === false)
        {
            $response->error(t('project uuid missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $projectModel = new ProjectModel();

        $projectId = $projectModel->projectMember((int)$user['id'], $uuid);

        if ($projectId === false)
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
        
        $taskModel = new TaskModel();
        $tasks = $taskModel->get(['*'], ['project_id' => $projectId], $limit, $offset);
        
        $response->success(['tasks' => $tasks]);      
    }
}