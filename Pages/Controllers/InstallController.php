<?php 

namespace Kantodo\Controllers;

use Kantodo\Core\{
    Application,
    Controller,
    Database\Connection,
    Validation\Data
};
use Kantodo\Views\InstallView;


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

        // validation

        $adminFirstname = Data::FormatName($body['adminName']);
        $adminLastname  = Data::FormatName($body['adminSurname']);
        $adminEmail     = filter_var($body['adminEmail'], FILTER_SANITIZE_EMAIL);
        $adminPass      = $body['adminPass'];


        if ($adminFirstname === false)
        {
            Application::$APP->response->AddResponseError("Invalid firstname");
            Application::$APP->response->OutputResponse();
            exit;
        }

        if ($adminLastname === false)
        {
            Application::$APP->response->AddResponseError("Invalid lastname");
            Application::$APP->response->OutputResponse();
            exit;
        }


        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) 
        {
            Application::$APP->response->AddResponseError("Invalid email");
            Application::$APP->response->OutputResponse();
            exit;
        }

        if (!Data::IsValidPassword($adminPass)) 
        {
            Application::$APP->response->AddResponseError("Invalid password");
            Application::$APP->response->OutputResponse();
            exit; 
        }

        $adminPassHash = Data::HashPassword($adminPass, $adminEmail);

        // insert to admin to db       




        Application::$APP->response->SetResponseData(true);
        Application::$APP->response->OutputResponse();
    }
}



?>