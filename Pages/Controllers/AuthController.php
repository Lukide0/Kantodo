<?php 
namespace Kantodo\Controllers;

use Kantodo\Core\{
    Application,
    Auth,
    Controller,
    Validation\Data
};

use Kantodo\Views\AuthView;

class AuthController extends Controller
{
    public function authenticate() 
    {
        $body = Application::$APP->request->getBody();

        if (!empty($body['get']['path']) && !Data::isURLExternal($body['get']['path'])) 
        {
            $this->renderView(AuthView::class, ['path' => $body['get']['path']]);
            return;
        }
        $this->renderView(AuthView::class);

    }

    public function signOut()
    {
        Auth::signOut();
        Application::$APP->response->setLocation('/auth');
    }

    public function signIn() 
    {
        $body = Application::$APP->request->getBody();

        $path = (isset($body['get']['path']) && Data::isURLExternal($body['get']['path']) === false) ? $body['get']['path'] : '';

        if(Data::isEmpty($body['post'], ['signInEmail', 'signInPassword'])) 
        {
            $empty = [];

            if (!empty($body['post']['signInEmail']))
                Application::$APP->session->addFlashMessage('userEmail', $body['post']['signInEmail']);
            else
                $empty['signInEmail'] = 'Empty field';

            if (empty($body['post']['signInPassword']))
                $empty['signInPassword'] = 'Empty field';

            Application::$APP->session->addFlashMessage('signInErrors', $empty);


            if (!empty($path))
            {
                Application::$APP->response->setLocation("/auth?path={$path}");
                exit;
            }        
            Application::$APP->response->setLocation('/auth');
            exit;
            
        }
        $status = Auth::signIn($body['post']['signInEmail'], $body['post']['signInPassword']);

        if (!$status) 
        {         
            Application::$APP->session->regenerateID();
            
            if (!empty($path))
            {
                Application::$APP->response->setLocation("{$body['get']['path']}");
                exit;
            }
            Application::$APP->response->setLocation('/');
            exit;

        }
        Application::$APP->response->setLocation("/auth?path={$path}");
        exit;

    }

    public function createAccount()
    {
        echo 'CREATE ACCOUNT';
    }
}