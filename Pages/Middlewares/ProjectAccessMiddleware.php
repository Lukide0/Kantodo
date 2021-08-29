<?php

namespace Kantodo\Middlewares;

use function Kantodo\Core\Functions\base64DecodeUrl;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractMiddleware;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TeamModel;

/**
 * Middleware na autentizaci
 */
class ProjectAccessMiddleware extends AbstractMiddleware
{

    const TEAM_ID_EXPIRATION    = 30 * 60; // 30 min
    const PROJECT_ID_EXPIRATION = 15 * 60; // 15 min

    /**
     * Vykoná middleware
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function execute(array $params = [])
    {
        $session = Application::$APP->session;

        // prazdné tým id
        if (empty($params['teamID'])) {
            Application::$APP->response->setLocation();
            exit;
        }

        $rawTeamID = $params['teamID'];

        $teamID = base64DecodeUrl($rawTeamID);
        $userID = Application::$APP->session->get('user')['id'];

        if (!filter_var($teamID, FILTER_VALIDATE_INT)) {
            Application::$APP->response->setLocation();
            exit;
        }

        // prázdná session s id týmu
        if ($session->get($teamID, false) === false) {
            $teamModel = new TeamModel();

            $pos = $teamModel->getTeamPosition($teamID, $userID);

            if (empty($pos)) {
                Application::$APP->response->setLocation();
                exit;
            }

            $session->set($teamID, $pos, time() + self::TEAM_ID_EXPIRATION);
        }

        if (empty($params['projID'])) {
            return;
        }

        $rawProjID = $params['projID'];
        $projID    = base64DecodeUrl($rawProjID);

        if (!filter_var($projID, FILTER_VALIDATE_INT)) {
            Application::$APP->response->setLocation("/team/{$rawTeamID}");
            exit;
        }

        // prázdná session s id projektu
        if ($session->get($projID, false) === false) {
            $projModel  = new ProjectModel();
            $userTeamID = $session->get($teamID)['id'];

            $pos = $projModel->getProjectPosition($projID, $userTeamID);

            if ($pos === false) {
                Application::$APP->response->setLocation("/team/{$rawTeamID}");
                exit;
            }

            $session->set($projID, $pos, time() + self::PROJECT_ID_EXPIRATION);
        }
    }
}
