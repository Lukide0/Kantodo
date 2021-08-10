<?php

declare (strict_types = 1);

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Auth;
use Kantodo\Core\Base\AbstractController;
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
        if (!empty($body['get']['path']) && !Data::isURLExternal($body['get']['path'])) {
            $this->renderView(AuthView::class, ['path' => urlencode($body['get']['path'])]);
            return;
        }
        $this->renderView(AuthView::class);
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

        $path = (isset($body['get']['path']) && Data::isURLExternal($body['get']['path']) === false) ? $body['get']['path'] : '';

        // prázdné email nebo heslo
        if (Data::isEmpty($body['post'], ['signInEmail', 'signInPassword'])) {
            $empty = [];

            // email
            if (!empty($body['post']['signInEmail'])) {
                Application::$APP->session->addFlashMessage('userEmail', $body['post']['signInEmail']);
            } else {
                $empty['signInEmail'] = 'Empty field';
            }

            // heslo
            if (empty($body['post']['signInPassword'])) {
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
        $status = Auth::signIn($body['post']['signInEmail'], $body['post']['signInPassword']);

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

        Application::$APP->session->addFlashMessage('userEmail', $body['post']['signInEmail']);

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
