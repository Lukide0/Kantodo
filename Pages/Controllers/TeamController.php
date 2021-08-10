<?php

namespace Kantodo\Controllers;

use function Kantodo\Core\Functions\base64_decode_url;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;

use Kantodo\Core\Validation\Data;
use Kantodo\Middlewares\ProjectAccessMiddleware;
use Kantodo\Models\TeamModel;

/**
 * Třída na práci s týmem
 */
class TeamController extends AbstractController
{
    /**
     * Akce na vytvoření týmu
     *
     * @return  void
     */
    public function createTeam()
    {
        $body     = Application::$APP->request->getBody();
        $response = Application::$APP->response;

        if (Data::isEmpty($body['post'], ['teamName'])) {
            $response->addResponseError('Empty field');
            $response->outputResponse();
            exit;
        }

        $desc = $body['post']['teamDesc'] ?? '';

        $user   = Application::$APP->session->get('user', false);
        $userID = $user['id'];

        $teamModel = new TeamModel();

        $id = $teamModel->create($body['post']['teamName'], $userID, $desc, false);

        if ($id === false) {
            $response->addResponseError('Server error');
            $response->outputResponse();
            exit;
        }

        $response->setResponseData(true);
        $response->outputResponse();
        exit;
    }

    /**
     * Zobrazení týmu
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function viewTeam(array $params = [])
    {
        $projectAccess = new ProjectAccessMiddleware();
        $projectAccess->execute($params);

        $rawTeamID = $params['teamID'];
        $teamID    = base64_decode_url($params['teamID']);

        $teamModel = new TeamModel();

        $params = [
            'tabs'     => self::getTabs($rawTeamID),
            'teamID'   => $teamID,
            'teamInfo' => $teamModel->getInfo($teamID),
        ];

        $this->renderView(TeamView::class, $params, ClientLayout::class);
    }

    /**
     * Taby v menu
     *
     * @param   string  $teamID
     *
     * @return  array  array s taby
     */
    public static function getTabs(string $teamID)
    {
        $path = Application::$URL_PATH;

        return [
            [
                'name' => "Projects",
                'path' => "{$path}/team/{$teamID}/project",
            ],
        ];
    }
}
