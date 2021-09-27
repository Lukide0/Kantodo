<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Core\Request;
use Kantodo\Widgets\Form;
use Kantodo\Widgets\Input;

/**
 * Přihlášení a registrace
 */
class AuthView implements IView
{

    public function render(array $params = [])
    {
        $authType = (isset($params['type']) && $params['type'] == 'register') ? 'right' : '';
        $fromURL  = (isset($params['path'])) ? '?path=' . $params['path'] : '';
        // TODO: frontend error
        $registerForm = new Form();

        $email  = Application::$APP->session->getFlashMessage('userEmail', '');
        $errors = Application::$APP->session->getFlashMessage('signInErrors', []);

?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/main.min.css">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/auth.min.css">
            <script src="<?= Application::$SCRIPT_URL ?>/main.js" ></script>
        </head>
        <body>
            <div class="container center middle full">
                <div class="auth">
                    <h2>Vítejte v Kantodo!</h2>
                    <?= Form::start('/auth/signin', Request::METHOD_POST, 'container full-width middle') ?>
                        <?= Form::tokenCSRF() ?>
                        <?= Input::text('signInEmail', 'Email', ['classes' => 'full-width']); ?>
                        <?= Input::password('signInPassword', 'Heslo', ['classes' => 'full-width']); ?>
                        <button class="primary full-width center big space-huge-top space-huge-bottom">Přihlásit</button>
                        <a href="?AAA" target="_blank" rel="noopener noreferrer" class="space-small-bottom">Zapomenuté heslo?</a>
                        <p>Nemáte účet? <a href="#" onclick="let x=document.querySelectorAll('.auth > .container'); x[0].style.display='none'; x[1].style.display='flex';">Registrujte se!</a></p>
                    <?= Form::end() ?>
                    <div class="container full-width middle" style="display: none;">
                        <div class="row full-width h-space-between">
                            <label class="text-field outline space-big-right">
                                <div class="field">
                                    <span>Jméno</span>
                                    <input type="text">
                                </div>
                            </label>
                            <label class="text-field outline">
                                <div class="field">
                                    <span>Příjmení</span>
                                    <input type="text">
                                </div>
                            </label>
                        </div>
                        <label class="text-field outline space-big-bottom space-big-top full-width">
                            <div class="field">
                                <span>Email</span>
                                <input type="text">
                            </div>
                        </label>
                        <div class="row full-width h-space-between">
                            <label class="text-field outline space-big-right">
                                <div class="field">
                                    <span>Heslo</span>
                                    <input type="password">
                                </div>
                            </label>
                            <label class="text-field outline">
                                <div class="field">
                                    <span>Heslo znuvu</span>
                                    <input type="password">
                                </div>
                            </label>
                        </div>
                        <button class="primary full-width center big space-huge-top space-huge-bottom">Registrovat se</button>
                        <p>Máte účet? <a href="#" onclick="let x=document.querySelectorAll('.auth > .container'); x[1].style.display='none'; x[0].style.display='flex';">Přihlaste se!</a></p>
                    </div>
                </div>
            </div>
        </body>

        </html>
<?php
    }
}

?>