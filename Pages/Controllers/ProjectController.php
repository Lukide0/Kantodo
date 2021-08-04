<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Controller;
use Kantodo\Middlewares\TeamAccessMiddleware;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TeamModel;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\ProjectsListView;
use Kantodo\Core\Validation\Data;

use function Kantodo\Core\base64_decode_url;

class ProjectController extends Controller
{
    public function __construct() {
        $this->registerMiddleware(new TeamAccessMiddleware());
    }

    public function createProject(array $params = [])
    {
        $teamID = base64_decode_url($params['teamID']);
        $response = Application::$APP->response;
        $session = Application::$APP->session;

        $body = Application::$APP->request->getBody();



        if (Data::isEmpty($body['post'], ['projName', 'projDesc'])) 
        {
            $response->addResponseError("Empty field|s");
            $response->outputResponse();
            exit;
        }
        


        $projectModel = new ProjectModel();

        $status = $projectModel->create($teamID, $session->get("user")['id'], $body['post']['projName'], $body['post']['projDesc']);
        


        var_dump($status);
        //$response->outputResponse();
    }

    public function projectsList(array $params = [])
    {
        // team tabs
        $params['tabs'] = TeamController::getTabs($params['teamID']);

        // team id 
        $params['teamID'] = base64_decode_url($params['teamID']);


        $projectModel = new ProjectModel();
        $teamModel = new TeamModel();

        // array of projects
        $params['projects'] = $projectModel->getTeamProjectList($params['teamID']);

        // team uuid
        $params['uuid'] = $teamModel->get(['uuid'], ['team_id' => $params['teamID']], 1)[0]['uuid'];

        $this->renderView(ProjectsListView::class, $params, ClientLayout::class);
    }
}



?>