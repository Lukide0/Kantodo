<?php 

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Controller;
use Kantodo\Views\InstallView;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Validation\Data;


class InstallController extends Controller
{
    public function Install($method = 'get')
    {
        if ($method == 'get')
            $this->RenderView(InstallView::class);

        else if ($method == 'post')
            $this->InstallAction();
    }

    private function InstallAction() 
    {
        Application::$APP->response->SetContentType();
        $body = Application::$APP->request->GetBody();

        $keys = ['dbName', 'dbUser', 'dbHost', 'adminName', 'adminSurname', 'adminEmail', 'adminPass'];

        $emptyKeys = Data::Empty($body, $keys);
        Data::SetIfNotSet($body, ['dbPass', 'dbPrefix'], "");
        
        if (count($emptyKeys) != 0) 
        {
            Application::$APP->response->AddResponseError("Empty field|s");
            Application::$APP->response->OutputResponse();
            exit;
        }
        $connectionStatus = Connection::TryConnect("mysql:host={$body['dbHost']};dbname={$body['dbName']}", $body['dbUser'], $body['dbPass'] ?? "");

        if (!$connectionStatus) 
        {
            Application::$APP->response->AddResponseError("Could not connect to database");
            Application::$APP->response->OutputResponse();
            exit; 
        }
        
        Application::$APP->response->AddResponseData("OK");
        Application::$APP->response->OutputResponse();
    }
}



?>