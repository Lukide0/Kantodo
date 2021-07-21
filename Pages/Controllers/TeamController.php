<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\{
    Application,
    Controller,
    Validation\Data
};
use Kantodo\Models\TeamModel;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\TeamsListView;

class TeamController extends Controller
{
    public function createTeam()
    {
        $body = Application::$APP->request->getBody();

        $response = Application::$APP->response;

        if (Data::isEmpty($body['post'], ['teamName'])) 
        {
            $response->addResponseError('Empty field');
            $response->outputResponse();
            exit;
        }

        $desc = $body['post']['teamDesc'] ?? '';

        $teamModel = new TeamModel();
        $id = $teamModel->create($body['post']['teamName'], $desc);

        if ($id === false) 
        {
            $response->addResponseError('Server error');
            $response->outputResponse();
            exit;
        }
        $userID = Application::$APP->session->get('userID');

        $status = $teamModel->setUserPosition($userID, $id, 'admin');

        if ($status === false) 
        {
            $response->addResponseError('Server error');
            $response->outputResponse();
            exit;
        }
        $response->setResponseData(true);
        $response->outputResponse();
        exit;
    }
}



?>