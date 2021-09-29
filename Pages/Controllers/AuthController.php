<?php

declare(strict_types=1);

namespace Kantodo\Controllers;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Session;
use Kantodo\Core\Validation\Data;
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
                $empty['signInEmail'] = 'Empty field';
            }

            // heslo
            if (empty($body[Request::METHOD_POST]['signInPassword'])) {
                $empty['signInPassword'] = 'Empty field';
            }

            Application::$APP->session->addFlashMessage('signInErrors', $empty);

            // přesměrování zpět
            if (!empty($path)) {
                $path = urlencode($path);
                Application::$APP->response->setLocation("/auth?path={$path}");
                exit;
            }
            Application::$APP->response->setLocation('/auth');
            exit;
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
        echo 'CREATE ACCOUNT';
    }
}
