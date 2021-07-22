<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\{
    Application,
    Controller,
    Validation\Data
};
use Kantodo\Models\TeamModel;

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

        $userID = Application::$APP->session->get("userID", false);

        if ($userID === false)
        {
            $response->addResponseError('Server error');
            $response->outputResponse();
            exit;
        }

        $teamModel = new TeamModel();
        
        $id = $teamModel->create($body['post']['teamName'], $userID, $desc, false);

        if ($id === false) 
        {
            $response->addResponseError('Server error');
            $response->outputResponse();
            exit;
        }

        $response->setResponseData(true);
        $response->outputResponse();
        exit;
    }

    public function viewTeam()
    {
        # code...
    }
}



?>