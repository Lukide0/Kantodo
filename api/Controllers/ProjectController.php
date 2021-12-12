<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Core\Validation\DataType;
use Kantodo\Models\ProjectModel;

use function Kantodo\Core\Functions\base64DecodeUrl;
use function Kantodo\Core\Functions\base64EncodeUrl;
use function Kantodo\Core\Functions\t;

class ProjectController extends AbstractController
{
    /**
     * Akce na vytvoření projektu
     *
     * @return  void
     */
    public function create()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $session = API::$API->session;

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
            return;
        }

        $response->success([
            'project' => 
                [
                    'uuid' => $status['uuid'],
                    'uuidSafe' => base64EncodeUrl($status['uuid'])
                ]
            ],
            Response::STATUS_CODE_CREATED
        );
    }

    /**
     * Akce na vytvoření týmu
     *
     * @return  void
     */
    public function join()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $session = API::$API->session;

        if (empty($body[Request::METHOD_POST]['code'])) 
        {
            $response->fail(['code' => t('empty', 'api')]);
            exit;
        }

        $projectCode = $body[Request::METHOD_POST]['code'];
        $user = $session->get('user');

        if (empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        $userID = (int)$user['id'];

        $projectModel = new ProjectModel();
        
        
        $projectID = $projectModel->getProjectByCode($projectCode);

        if ($projectID === false) 
        {
            $response->error(t('project_code_is_not_valid', 'api'));
            exit;
        }

        $posID = $projectModel->getPosition('guest');

        if ($posID === false) 
        {
            $posID = $projectModel->createPosition('guest');

            if ($posID === false) 
            {
                $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
                exit;
            }
        }
        
        $status = $projectModel->setUserPosition($userID, $projectID, $posID);
        
        
        if ($status === false) 
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        } else 
        {
            $response->success();
        }
    }


    /**
     * Akce na získání kódu týmu
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    public function getCode(array $params = [])
    {
        $response = API::$API->response;
        $session = API::$API->session;
        
        if (empty($params['projectUUID']))
            $response->error(t('project uuid missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            
        $uuid = base64DecodeUrl($params['projectUUID']);
        if ($uuid === false) 
        {
            $response->error(t('project uuid missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $user = $session->get('user');

        if (!DataType::number($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        $id   = $user['id'];
        
        if (ProjectModel::hasPrivTo('addPeople', (int)$id, $uuid) !== true) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
        }

        $projModel = new ProjectModel();
        
        $code = $projModel->getOrCreateCode($uuid);
        
        if ($code === false) 
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
        else {
            $response->success(['code' => $code]);
        }
    }
}
