<?php

declare(strict_types=1);

namespace Kantodo\Middlewares;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractMiddleware;
use Kantodo\Core\Exception\KantodoException;
use Kantodo\Core\Validation\DataType;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\UserModel;

use function Kantodo\Core\Functions\base64DecodeUrl;

class ProjectMiddleware extends AbstractMiddleware 
{
    public function execute(array $params = [])
    {
        $user = Auth::getUser();

        if ($user === null) 
        {
            Auth::signOut();
            Application::$APP->response->setLocation('/auth');
            exit;
        }

        $id = $user['id'];

        if (!DataType::wholeNumber($id)) 
        {
            Application::$APP->response->setLocation();
            exit;
        }

        $projModel = new ProjectModel();

        $projUUID = base64DecodeUrl($params['projectUUID']);

        $project = $projModel->getSingle(['project_id', 'name', 'is_open', 'is_public'], ['uuid' => $projUUID]);

        if ($project === false) 
        {
            Application::$APP->response->setLocation();
            exit;
        }

        $params['project'] = $project;

        $pos       = $projModel->getProjectPosition((int) $project['project_id'], (int) $id);
        if ($pos === false) {
            Application::$APP->response->setLocation();
            exit;
        }

        $params['priv'] = $projModel->getPositionPriv($pos);
        $params['members'] = $projModel->getMembers((int)$project['project_id']);

        $ids = [];

        if ($params['members'] === false) 
        {
            throw new KantodoException("", 500);
        }

        foreach ($params['members'] as $value) {
            $ids[] = $value['project_position_id'];
        }

        $params['positions'] = $projModel->getPositionsByID($ids);

        return $params;
    }

}