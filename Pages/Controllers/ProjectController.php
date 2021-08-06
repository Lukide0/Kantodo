<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Controller;
use Kantodo\Middlewares\ProjectAccessMiddleware;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TeamModel;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\ProjectsListView;
use Kantodo\Core\Validation\Data;
use Kantodo\Views\ProjectView;

use function Kantodo\Core\base64_decode_url;

class ProjectController extends Controller
{
    public function __construct() {
        $this->registerMiddleware(new ProjectAccessMiddleware());
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

        $id = $projectModel->create($teamID, $session->get("user")['id'], $body['post']['projName'], $body['post']['projDesc']);
        
        if ($id === false) 
        {
            $response->addResponseError("Server error");
            $response->outputResponse();
            exit;
        }

        $response->setResponseData(['id' => $id]);
        $response->outputResponse();
    }

    public function viewProject(array $params = [])
    {
        $projModel = new ProjectModel();

        $projID = base64_decode_url($params['projID']);

        $params['membersInitials'] = $projModel->getMembersInitials($projID);
        $params['columns'] = $projModel->getColumns($projID);


        $this->renderView(ProjectView::class, $params, ClientLayout::class);
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