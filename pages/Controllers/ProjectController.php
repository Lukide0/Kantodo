<?php

namespace Kantodo\Controllers;

use function Kantodo\Core\Functions\base64DecodeUrl;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Validation\Data;
use Kantodo\Middlewares\ProjectAccessMiddleware;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TaskModel;
use Kantodo\Models\TeamModel;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\ProjectsListView;
use Kantodo\Views\ProjectView;

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
        $teamID   = base64DecodeUrl($params['teamID']);
        $response = Application::$APP->response;
        $session  = Application::$APP->session;

        $body = Application::$APP->request->getBody();

        if (Data::isEmpty($body[Request::METHOD_POST], ['projName', 'projDesc'])) {
            $response->addResponseError("Empty field|s");
            $response->outputResponse();
            exit;
        }

        $projectModel = new ProjectModel();

        // vytvoření projektu
        $id = $projectModel->create($teamID, $session->get("user")['id'], $body[Request::METHOD_POST]['projName'], $body[Request::METHOD_POST]['projDesc']);

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

        $projID = base64DecodeUrl($params['projID']);

        // jméno týmu
        $params['teamName'] = $projModel->getSingle(['name'], ['project_id' => $projID])['name'];

        // iniciály členů projektu
        $params['membersInitials'] = $projModel->getMembersInitials($projID);

        // sloupce
        $params['columns'] = $projModel->getColumns($projID);

        $taskModel = new TaskModel();

        for ($i = 0; $i < count($params['columns']); $i++) {
            $params['columns'][$i]['tasks'] = $taskModel->get(
                [
                    'name',
                    'description' => 'desc',
                    'priority', 'completed',
                    'end_date',
                    'index',
                ],
                [
                    'column_id' => $params['columns'][$i]['id'],
                ]
            );
        }

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

        $params['teamID'] = base64DecodeUrl($params['teamID']);

        $projectModel = new ProjectModel();
        $teamModel    = new TeamModel();

        // array projektů
        $params['projects'] = $projectModel->getTeamProjectList($params['teamID']);

        // uuid
        $params['uuid'] = $teamModel->getSingle(['uuid'], ['team_id' => $params['teamID']])['uuid'];

        $this->renderView(ProjectsListView::class, $params, ClientLayout::class);
    }
}
