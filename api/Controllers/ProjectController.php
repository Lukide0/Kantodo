<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Auth\Auth;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Core\Validation\Data;
use Kantodo\Core\Validation\DataType;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\UserModel;

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
    
        if (empty($body[Request::METHOD_POST]['name'])) 
        {
            $response->fail(['name' => t('empty', 'api')]);
        }

        $projectName = $body[Request::METHOD_POST]['name'];
        $user = Auth::getUser();

        if ($user === null || empty($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        $projModel = new ProjectModel();

        $status = $projModel->create((int)$user['id'], $projectName);
        if ($status === false) 
        {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            return;
        }

        $response->success([
            'project' => 
                [
                    'uuid' => base64EncodeUrl($status['uuid'])
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

        if (empty($body[Request::METHOD_POST]['code'])) 
        {
            $response->fail(['code' => t('empty', 'api')]);
            exit;
        }

        $projectCode = $body[Request::METHOD_POST]['code'];
        $user = Auth::getUser();

        if ($user === null || empty($user['id'])) 
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
        
        $project = $projectModel->getSingle(['name', 'uuid'], ['project_id' => $projectID]);
        
        if ($status === false || $project === false) 
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        } else 
        {
            $response->success([
                'project' => 
                [
                    'name' => $project['name'],
                    'uuid' => base64EncodeUrl($project['uuid'])
                ]
            ]);
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
        
        if (empty($params['projectUUID']))
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            
        $uuid = base64DecodeUrl($params['projectUUID']);
        if ($uuid === false) 
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $user = Auth::getUser();

        if ($user === null || !DataType::number($user['id'])) 
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


    /**
     * Akce na změnění pozice uživatele
     *
     * @return  void
     */
    public function changePosition()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $user = Auth::getUser();
        
        $keys = ['project', 'user', 'position'];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        if ($user === null || !DataType::number($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }
        $uuid = base64DecodeUrl($body[Request::METHOD_POST]['project']);
        $id   = $user['id'];
        $email = $body[Request::METHOD_POST]['user'];
        $position = $body[Request::METHOD_POST]['position'];

        if ($uuid === false)
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if (isset(ProjectModel::POSITIONS[$position]) === false) 
        {
            $response->error(t('position_does_not_exists', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        } else if ($position === 'admin') 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $projModel = new ProjectModel();
        $projectID = 0;
        
        if (ProjectModel::hasPrivTo('changePeoplePosition', (int)$id, $uuid, $projectID) !== true) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $userModel = new UserModel();
        $member = $userModel->getSingle(['user_id'], ['email' => $email]);

        if ($member === false) 
        {
            $response->error(t('user_is_not_member', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $memberID = (int)$member['user_id'];
        
        // není členem projektu
        $memberPos = $projModel->getProjectPosition($projectID, $memberID);
        if ($memberPos === false) 
        {
            $response->error(t('user_is_not_member', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }
        // admin nemůže být změněn
        else if ($memberPos == "admin") 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $status = $projModel->updatePosition($memberID, $projectID, $position);


        if ($status) 
        {
            $response->success([]);
        } 
        else
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Akce na odstranění uživatele z projektu
     *
     * @return  void
     */
    public function deleteUser()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $user = Auth::getUser();
        
        $keys = ['project', 'user'];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        if ($user === null || !DataType::number($user['id'])) 
        {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }
    
        $uuid = base64DecodeUrl($body[Request::METHOD_POST]['project']);
        $id   = $user['id'];
        $email = $body[Request::METHOD_POST]['user'];

        

        if ($uuid === false)
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $projModel = new ProjectModel();
        $projectID = 0;
     
        if (ProjectModel::hasPrivTo('changePeoplePosition', (int)$id, $uuid, $projectID) !== true) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $userModel = new UserModel();
        $member = $userModel->getSingle(['user_id'], ['email' => $email]);

        // neexistuje
        if ($member === false) 
        {
            $response->error(t('user_is_not_member', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $memberID = (int)$member['user_id'];
        
        // není členem projektu
        $memberPos = $projModel->getProjectPosition($projectID, $memberID);
        if ($memberPos === false) 
        {
            $response->error(t('user_is_not_member', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        } 
        // admin nemůže být vyhozen
        else if ($memberPos == "admin") 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $status = $projModel->removeUser($memberID, $projectID);
        if ($status) 
        {
            $response->success([]);
        } 
        else
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }

    // TODO: frontend
    /**
     * Akce na smazání projektu
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    public function remove(array $params = [])
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $keys = [
            'email',
            'password',
        ];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
        }

        $email = $body[Request::METHOD_POST]['email'];
        $password = Auth::hashPassword($body[Request::METHOD_POST]['password'], $email);

        $user = Auth::getUser();

        if ($user == null || $user['email'] != $email) 
        {
            $response->error(t('invalid_credentials'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }
        
        if (empty($body[Request::METHOD_POST]['project']))
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
        
        $uuid = base64DecodeUrl($body[Request::METHOD_POST]['project']);
        if ($uuid === false) 
        {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }



        $userModel = new UserModel();
        $exists = $userModel->exists(['email' => $email, 'password' => $password]);

        if (!$exists) 
        {
            $response->error(t('invalid_credentials'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $id = (int)$user['id'];


        $projModel = new ProjectModel();

        $project = $projModel->getSingle(['project_id'], ['uuid' => $uuid]);

        if ($project === false) 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $projectID = (int)$project['project_id'];

        $memberPos = $projModel->getProjectPosition($projectID, $id);

        if ($memberPos !== 'admin') 
        {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }


        $status = $projModel->delete($projectID);
        if ($status) 
        {
            $response->success([]);
        } 
        else
        {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }
}
