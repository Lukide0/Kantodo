<?php 

namespace Kantodo\Middlewares;

use Kantodo\Core\{
    BaseMiddleware,
    Application
};
use Kantodo\Models\TeamModel;


use function Kantodo\Core\base64_decode_url;

class TeamAccessMiddleware extends BaseMiddleware
{
    public function execute(array $args = [])
    {
        $session = Application::$APP->session;

        if (empty($args['teamID'])) 
        {
            Application::$APP->response->setLocation();
            exit;
        }

        $rawTeamID = $args['teamID'];

        $teamID = base64_decode_url($rawTeamID);

        if (!filter_var($teamID, FILTER_VALIDATE_INT)) 
        {
            Application::$APP->response->setLocation();
            exit;
        }

        if ($session->get($teamID, false) === false) 
        {
            $teamModel = new TeamModel();
    
            $pos = $teamModel->getUserTeamPosition(Application::$APP->session->get('user')['id'], $teamID);
    
            if (empty($pos)) 
            {
                Application::$APP->response->setLocation();
                exit;
            }

            $session->set($teamID, $pos['name'], time() + 5*60);
        }
    }
}



?>