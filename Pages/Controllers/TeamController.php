<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\{
    Application,
    Controller,
    Validation\Data
};
use Kantodo\Middlewares\TeamAccessMiddleware;

use Kantodo\Models\TeamModel;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\TeamView;

use function Kantodo\Core\base64_decode_url;

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

        $user = Application::$APP->session->get('user', false);

        if ($user === false)
        {
            $response->addResponseError('Server error');
            $response->outputResponse();
            exit;
        }

        $userID = $user['id'];

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

    
    public function viewTeam($args = [])
    {
        $teamAccess = new TeamAccessMiddleware();
        $teamAccess->execute($args);
        
        $rawTeamID = $args['teamID'];
        $teamID = base64_decode_url($args['teamID']);
        
        $params = [
            'tabs' => self::getTabs($rawTeamID),
            'teamID' => $teamID
        ];
        
        $this->renderView(TeamView::class, $params, ClientLayout::class);
    }

    public static function getTabs(string $teamID)
    {
        $path = Application::$URL_PATH;
    
        return [
            [
                'name' => "Projects",
                'path' => "{$path}/team/{$teamID}/project"
            ]
        ];
    }
}



?>