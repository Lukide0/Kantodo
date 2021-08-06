<?php 

namespace Kantodo\Middlewares;

use Kantodo\Core\{
    BaseMiddleware,
    Application
};
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TeamModel;


use function Kantodo\Core\base64_decode_url;

class ProjectAccessMiddleware extends BaseMiddleware
{

    const TEAM_ID_EXPIRATION = 30 * 60; // 30 min
    const PROJECT_ID_EXPIRATION = 15 * 60; // 15 min

    public function execute(array $params = [])
    {
        $session = Application::$APP->session;

        if (empty($params['teamID'])) 
        {
            Application::$APP->response->setLocation();
            exit;
        }

        $rawTeamID = $params['teamID'];
        
        $teamID = base64_decode_url($rawTeamID);
        $userID = Application::$APP->session->get('user')['id'];

        if (!filter_var($teamID, FILTER_VALIDATE_INT)) 
        {
            Application::$APP->response->setLocation();
            exit;
        }


        if ($session->get($teamID, false) === false) 
        {
            $teamModel = new TeamModel();
    
            $pos = $teamModel->getTeamPosition($teamID, $userID);
    
            if (empty($pos)) 
            {
                Application::$APP->response->setLocation();
                exit;
            }

            $session->set($teamID, $pos, time() + self::TEAM_ID_EXPIRATION);
        }

        if (empty($params['projID']))
            return;


        $rawProjID = $params['projID'];
        $projID = base64_decode_url($rawProjID);



        if (!filter_var($projID, FILTER_VALIDATE_INT)) 
        {
            Application::$APP->response->setLocation("/team/{$rawTeamID}");
            exit;
        }
        
        
        if ($session->get($projID, false) === false) 
        {
            $projModel = new ProjectModel();
            $userTeamID = $session->get($teamID)['id'];
            
            $pos = $projModel->getProjectPosition($projID, $userTeamID);

            if (empty($pos)) 
            {
                Application::$APP->response->setLocation("/team/{$rawTeamID}");
                exit;
            }
            

            $session->set($projID, $pos['name'], time() + self::PROJECT_ID_EXPIRATION);
        }
    }
}



?>