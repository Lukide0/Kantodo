<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
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
        $appUrl   = Application::$URL_PATH;

        $signInAction = "{$appUrl}/auth/sign-in{$fromURL}";
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
            <link rel="stylesheet" href="Styles/flex.css">
            <link rel="stylesheet" href="styles/main.css">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
            <script src="scripts/components.js"></script>
            <script src="scripts/validation.js"></script>
            <script src="scripts/main.js"></script>
            <title>Přihlášení</title>
            <style>
                body {
                    all: unset;
                    background: rgb(var(--info));
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                }

                header {
                    all: unset;
                    background: rgb(0, 27, 41);
                    display: inline;
                    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.14), 0px 1px 18px rgba(0, 0, 0, 0.12), 0px 3px 5px -1px rgba(0, 0, 0, 0.2);
                }

                main {
                    all: unset;
                    width: 100%;
                    height: 100%;
                    display: flex;
                    clip-path: polygon(0 0, 50% 0, 40% 100%, 0 100%);
                    transition: clip-path 400ms ease-in-out;
                    background: white;
                }

                main.right {
                    clip-path: polygon(40% 0, 100% 0, 100% 100%, 50% 100%);
                }

                main>div.container {
                    padding: var(--gap-huge);
                }

                form>.row {
                    margin-bottom: var(--gap-huge);
                }

                h2 {
                    align-self: center;
                    margin-bottom: 3rem;
                    font-size: 2.5rem;
                }

                button {
                    margin-top: 2rem;
                }
            </style>
        </head>

        <body class="theme-light">
            <header>
                <h1>Kantodo</h1>
            </header>
            <main class="main-space-between <?=$authType;?>">
                <div class="col-5 container main-center">
                    <h2>Welcome back</h2>
                    <?=Form::start($signInAction);?>
                    <?=Form::tokenCSRF();?>
                    <div class="row main-center space-top">
                        <?=Input::text(
            'signInEmail',
            'Email',
            [
                'outline'      => true,
                'color'        => 'info',
                'value'        => $email,
                'error'        => $errors,
                'autocomplete' => Input::AUTOCOMPLETE_EMAIL,
            ]
        )
        ;?>
                    </div>
                    <div class="row main-center space-top">
                        <?=Input::password(
            'signInPassword',
            'Password',
            [
                'outline'      => true,
                'color'        => 'info',
                'value'        => $email,
                'error'        => $errors,
                'autocomplete' => Input::AUTOCOMPLETE_CURRENT_PASSWORD,
            ]
        )
        ;?>
                    </div>
                    <div class="row main-center space-top">
                        <button class="info long">Sign in</button>
                    </div>
                    <div class="row main-center space-top">
                        <button class="text flat" onclick="(function(event) { event.preventDefault(); let x = document.getElementsByTagName('main')[0]; x.classList.add('right'); switchInputDisable(x.querySelector('div'));  switchInputDisable(document.querySelector('main > div:nth-child(2)'), false)})(event)">Create
                            account</button>
                    </div>
                    <?=Form::end();?>
                </div>
                <div class="col-5 container main-center">
                    <h2>Welcome to Kantodo</h2>
                    <div class="row main-space-between">
                        <label class="text info-focus">
                            <input type="text" disabled required>
                            <span>Jmeno</span>
                            <div class="error-text"></div>
                        </label>
                        <label class="text info-focus">
                            <input type="text" disabled required>
                            <span>Prijmeni</span>
                            <div class="error-text"></div>
                        </label>
                    </div>
                    <div class="row">
                        <div class="container">
                            <label class="text info-focus">
                                <input type="text" disabled required>
                                <span>Email</span>
                            </label>
                            <div class="error-text"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="container">
                            <label class="text info-focus input-open">
                                <input type="password" name="registerPassword" data-password-validation="PasswordValidation" disabled required>
                                <span>Heslo</span>
                                <div class="input-close"><span class="material-icons-outlined" data-show="false" onclick="switchPasswordVisibility(event)">visibility</span></div>
                            </label>
                            <div class="error-text"></div>
                        </div>
                    </div>
                    <div class="row">
                        <ul class="requirements" data-password-requirements='registerPassword'>
                            <li data-error="MIN_LENGTH">At least 8 characters</li>
                            <li data-error='LOWERCASE_CHAR_COUNT'>One lowercase character</li>
                            <li data-error='UPPERCASE_CHAR_COUNT'>One uppercase character</li>
                            <li data-error='NUMBERS_COUNT'>One number</li>
                            <li data-error='SPECIAL_CHARS_COUNT'>One special character</li>
                        </ul>
                    </div>
                    <div class="row main-center">
                        <button class="info long">Create account</button>
                    </div>
                    <div class="row main-center">
                        <button class="text flat" onclick="(function() { document.getElementsByTagName('main')[0].classList.remove('right') })()">Sign
                            in</button>
                    </div>
                </div>
                </div>
            </main>
            <script>
                function passwordValidation(event, obj) {
                    let el = event.target;
                    let value = el.value;

                    let errors = validatePassword(value, {
                        'min': 8,
                        'lowercase': 1,
                        'uppercase': 1,
                        'number': 1,
                        'specialChar': 1
                    });
                    for (let i = 0; i < obj.parent.children.length; i++) {
                        const el = obj.parent.children[i];
                        el.classList.remove('error');
                        el.classList.add('success');
                    }


                    if (errors.length == 0)
                        return true;

                    errors.forEach(error => {
                        if (obj.hasOwnProperty(error)) {
                            let el = obj[error];

                            el.classList.add('error');
                        }
                    });


                    return false;
                }
            </script>
        </body>

        </html>
<?php
}
}

?>