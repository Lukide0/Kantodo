<?php

declare(strict_types=1);

namespace Kantodo\Controllers;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Session;
use Kantodo\Core\Validation\Data;
use Kantodo\Models\UserModel;
use Kantodo\Views\AuthView;

/**
 * Třída na autorizace
 */
class AuthController extends AbstractController
{
    /**
     * Autorizace uživatele
     *
     * @return  void
     */
    public function authenticate()
    {
        $body = Application::$APP->request->getBody();
        // cesta z které byl uživatel přesměrován
        if (empty($body[Request::METHOD_GET]['path']) || Data::isURLExternal($body[Request::METHOD_GET]['path'])) {
            $this->renderView(AuthView::class);
            return;
        }
        $this->renderView(AuthView::class, ['path' => urlencode($body[Request::METHOD_GET]['path'])]);

    }

    /**
     * Akce na odhlášení
     *
     * @return  void
     */
    public function signOut()
    {
        Auth::signOut();
        Application::$APP->response->setLocation('/auth');
    }

    /**
     * Akce na přihlášení
     *
     * @return  void
     */
    public function signIn()
    {
        $body = Application::$APP->request->getBody();

        $path = (isset($body[Request::METHOD_GET]['path']) && Data::isURLExternal($body[Request::METHOD_GET]['path']) === false) ? $body[Request::METHOD_GET]['path'] : '';
        if (!Application::$APP->request->isValidTokenCSRF()) {
            $path = urlencode($path); 
            Application::$APP->response->setLocation("/auth?path={$path}");
            exit;
        }
        // prázdné email nebo heslo
        if (Data::isEmpty($body[Request::METHOD_POST], ['signInEmail', 'signInPassword'])) {
            $empty = [];
            
            // email
            if (!empty($body[Request::METHOD_POST]['signInEmail'])) {
                Application::$APP->session->addFlashMessage('userEmail', $body[Request::METHOD_POST]['signInEmail']);
            } else {
                $empty[] = 'signInEmail';
            }
            // heslo
            if (empty($body[Request::METHOD_POST]['signInPassword'])) {
                $empty[] = 'signInPassword';
            }

            Application::$APP->session->addFlashMessage('authErrors', ['empty' => $empty]);

            $this->redirectBack($path);
        }

        /**
         * přihlášení uživatele
         *
         * @var bool true => pokud je zadáno správné heslo i email
         */
        $status = Auth::signIn($body[Request::METHOD_POST]['signInEmail'], $body[Request::METHOD_POST]['signInPassword']);
        
        if ($status) {

            Application::$APP->session->regenerateID();

            if (!empty($path)) {
                $path = urldecode($path);
                Application::$APP->response->setLocation("{$path}");
                exit;
            }
            Application::$APP->response->setLocation();
            exit;
        }

        Application::$APP->session->addFlashMessage('userEmail', $body[Request::METHOD_POST]['signInEmail']);

        Application::$APP->session->addFlashMessage('authErrors', ['empty' => []]);

        Application::$APP->response->setLocation("/auth?path={$path}");
        exit;
    }

    /**
     * Akce na vytvoření účtu
     *
     * @return void
     */
    public function createAccount()
    {
        $body = Application::$APP->request->getBody();
        Application::$APP->session->addFlashMessage('register', true);
        
        $path = (isset($body[Request::METHOD_GET]['path']) && Data::isURLExternal($body[Request::METHOD_GET]['path']) === false) ? $body[Request::METHOD_GET]['path'] : '';
        if (!Application::$APP->request->isValidTokenCSRF()) {
            $path = urlencode($path); 
            Application::$APP->response->setLocation("/auth?path={$path}");
            exit;
        }

        // email
        if (!empty($body[Request::METHOD_POST]['signUpEmail'])) {
            Application::$APP->session->addFlashMessage('userEmail', $body[Request::METHOD_POST]['signUpEmail']);
        }

        // jméno
        if (!empty($body[Request::METHOD_POST]['signUpName'])) {
            Application::$APP->session->addFlashMessage('userName', $body[Request::METHOD_POST]['signUpName']);
        }

        // příjmení
        if (!empty($body[Request::METHOD_POST]['signUpSurname'])) {
            Application::$APP->session->addFlashMessage('userSurname', $body[Request::METHOD_POST]['signUpSurname']);
        }

        $empty = Data::empty($body[Request::METHOD_POST], ['signUpName', 'signUpSurname', 'signUpEmail', 'signUpPassword', 'signUpPasswordAgain']);
        if (count($empty) != 0) {        

            Application::$APP->session->addFlashMessage('authErrors', ['empty' => $empty]);

            $this->redirectBack($path);
        }

        $firstname = Data::formatName($body[Request::METHOD_POST]['signUpName']);
        $lastname = Data::formatName($body[Request::METHOD_POST]['signUpSurname']);
        $email = $body[Request::METHOD_POST]['signUpEmail'];
        $pass = $body[Request::METHOD_POST]['signUpPassword'];
        $passAgain = $body[Request::METHOD_POST]['signUpPasswordAgain'];

        $errors = [];
        if ($pass != $passAgain) 
        {
            $errors['signUpPassword'] = 'passwords_do_not_match';
            $errors['signUpPasswordAgain'] = 'passwords_do_not_match';
        }

        if (!Data::isValidEmail($email)) 
        {
            $errors['signUpEmail'] = 'email_is_not_valid';
        }

        if (!Data::isValidPassword($pass, true, true, true))
        {
            $errors['signUpPassword'] = 'password_is_not_valid';
        }

        if ($firstname === false) 
        {
            $errors['signUpName'] = 'surname_is_not_valid';
        }

        if ($lastname === false) 
        {
            $errors['signUpSurname'] = 'surname_is_not_valid';
        }

        if (count($errors) != 0) 
        {
            Application::$APP->session->addFlashMessage('authErrors', ['validation' => $errors]);
            $this->redirectBack($path);
        }


        $userModel = new UserModel();

        $emailExists = $userModel->existsEmail($email);

        if ($emailExists) 
        {
            Application::$APP->session->addFlashMessage('authErrors', ['validation' => ['signUpEmail' => 'email_is_already_taken']]);
            $this->redirectBack($path);
        }

        $status = $userModel->create($firstname, $lastname, $email, Auth::hashPassword($pass, $email));

        if ($status === false) 
        {
            Application::$APP->session->addFlashMessage('authErrors', ['error' => 'something_went_wrong']);
            $this->redirectBack($path);
        }
        Application::$APP->session->addFlashMessage('register', false);
        $this->redirectBack($path);
        
    }

    private function redirectBack($path)
    {
        // přesměrování zpět
        if (!empty($path)) {
            $path = urlencode($path);
            Application::$APP->response->setLocation("/auth?path={$path}");
            exit;
        }
        Application::$APP->response->setLocation('/auth');
        exit;
    }
}
