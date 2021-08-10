<?php

namespace Kantodo\Controllers;

use function Kantodo\Core\Functions\base64_decode_url;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;

use Kantodo\Core\Validation\Data;
use Kantodo\Middlewares\ProjectAccessMiddleware;
use Kantodo\Models\ProjectModel;

use Kantodo\Models\TeamModel;

/**
 * Třída na práci s projektem
 */
class ProjectController extends AbstractController
{
    public function __construct()
    {
        $this->registerMiddleware(new ProjectAccessMiddleware());
    }

    /**
     * Akce na vytvoření projektu
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function createProject(array $params = [])
    {
        $teamID   = base64_decode_url($params['teamID']);
        $response = Application::$APP->response;
        $session  = Application::$APP->session;

        $body = Application::$APP->request->getBody();

        if (Data::isEmpty($body['post'], ['projName', 'projDesc'])) {
            $response->addResponseError("Empty field|s");
            $response->outputResponse();
            exit;
        }

        $projectModel = new ProjectModel();

        // vytvoření projektu
        $id = $projectModel->create($teamID, $session->get("user")['id'], $body['post']['projName'], $body['post']['projDesc']);

        if ($id === false) {
            $response->addResponseError("Server error");
            $response->outputResponse();
            exit;
        }

        $response->setResponseData(['id' => $id]);
        $response->outputResponse();
    }

    /**
     * Zobrazení projetu
     *
     * @param   array  $params  parametry
     *
     * @return  void
     */
    public function viewProject(array $params = [])
    {
        $projModel = new ProjectModel();

        $projID = base64_decode_url($params['projID']);

        // iniciály členů projektu
        $params['membersInitials'] = $projModel->getMembersInitials($projID);

        // sloupce
        $params['columns'] = $projModel->getColumns($projID);

        $this->renderView(ProjectView::class, $params, ClientLayout::class);
    }

    /**
     * Zobrazení listu s projekty
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function projectsList(array $params = [])
    {
        // tabs
        $params['tabs'] = TeamController::getTabs($params['teamID']);

        $params['teamID'] = base64_decode_url($params['teamID']);

        $projectModel = new ProjectModel();
        $teamModel    = new TeamModel();

        // array projektů
        $params['projects'] = $projectModel->getTeamProjectList($params['teamID']);

        // uuid
        $params['uuid'] = $teamModel->get(['uuid'], ['team_id' => $params['teamID']], 1)[0]['uuid'];

        $this->renderView(ProjectsListView::class, $params, ClientLayout::class);
    }
}
