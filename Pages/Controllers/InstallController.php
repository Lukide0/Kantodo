<?php 

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Controller;
use Kantodo\Views\InstallView;
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
        
        $keys = ['dbName', 'dbUser', 'dbHost', 'dbPass', 'adminName', 'adminSurname', 'adminEmail', 'adminPass'];
        
        $emptyKeys = Data::Empty($body, $keys);
        
        if (count($emptyKeys) != 0) 
        {
            Application::$APP->response->AddResponseError("Empty field|s");
            Application::$APP->response->OutputResponse();
            exit;
        }

    }
}



?>