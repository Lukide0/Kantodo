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

    /**
     * Akce na vytvoření projektu
     *
     * @param   array<mixed>  $params  parametry z url
     *
     * @return  void
     */
    public function createProject(array $params = [])
    {
        $teamID   = base64DecodeUrl($params['teamID']);
        $response = Application::$APP->response;
        $session  = Application::$APP->session;

        $body = Application::$APP->request->getBody();

        if (Data::isEmpty($body[Request::METHOD_POST], ['projName'])) {
            $response->addResponseError("Empty field|s");
            $response->outputResponse();
            exit;
        }

        $projectModel = new ProjectModel();

        // vytvoření projektu
        $id = $projectModel->create($session->get("user")['id'], $body[Request::METHOD_POST]['projName']);

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
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    public function viewProject(array $params = [])
    {
        $projModel = new ProjectModel();

        $projID = (int)base64DecodeUrl($params['projID']);

        

        $teamName = $projModel->getSingle(['name'], ['project_id' => $projID]);

        // jméno týmu
        if ($teamName != false) {
            $params['teamName'] = $teamName['name'];
        } else {
            $params['teamName'] = "";
        }

        // iniciály členů projektu
        $params['membersInitials'] = $projModel->getMembersInitials($projID);

        $this->renderView(ProjectView::class, $params, ClientLayout::class);
    }
}
