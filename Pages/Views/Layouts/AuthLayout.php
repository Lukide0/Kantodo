<?php 

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Layout;


class AuthLayout extends Layout
{
    public function Render(string $content = "", array $params = [])
    {
        $authType = (isset($params['type']) && $params['type'] == 'register') ? 'right' : '';

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="Styles/flex.css">
            <link rel="stylesheet" href="Styles/style.css">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
            <script src="Scripts/Components.js"></script>
            <title>Přihlášení</title>
            <style>
                body {
                    all: unset;
                    background: var(--info);
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
                    transition: clip-path 500ms ease-in;
                    background: var(--bg-third);
                }
                
                main.right {
                    clip-path: polygon(40% 0, 100% 0, 100% 100%, 50% 100%);
                }
                
                main>div.container {
                    padding: var(--gap-huge);
                }
                
                .container>.row {
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
        <body>
            <header>
                <h1>Kantodo</h1>
            </header>
            <main class="main-space-between">
                <div class="col-5 container main-center">
                    <h2>Welcome back</h2>
                    <div class="row main-center">
                        <label class="text info-focus">
                            <input type="text" required>
                            <span>Email</span>
                        </label>
                        <div class="error-text"></div>
                    </div>
                    <div class="row main-center">
                        <label class="text info-focus">
                            <input type="password" required>
                            <span>Heslo</span>
                        </label>
                        <div class="error-text"></div>
                    </div>
                    <div class="row main-center">
                        <button class="info long">Sign in</button>
                    </div>
                    <div class="row main-center">
                        <button class="text flat"
                            onclick="(function() { let x = document.getElementsByTagName('main')[0]; x.classList.add('right'); switchInputDisable(x.querySelector('div'));  switchInputDisable(document.querySelector('main > div:nth-child(2)'), false)})()">Create
                            account</button>
                    </div>
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
                                <input type="password" disabled required>
                                <span>Heslo</span>
                                <div class="input-close"><span class="material-icons-outlined" data-show="false" onclick="switchPasswordVisibility(event)">visibility</span></div>
                            </label>
                            <div class="error-text"></div>
                        </div>
                    </div>
                    <div class="row">
                        <ul class="requirements">
                            <li class="success">At least 8 characters</li>
                            <li>One lowercase character</li>
                            <li>One uppercase character</li>
                            <li class="error">One number</li>
                            <li>One special character</li>
                        </ul>
                    </div>
                    <div class="row main-center">
                        <button class="info long">Create account</button>
                    </div>
                    <div class="row main-center">
                        <button class="text flat"
                            onclick="(function() { document.getElementsByTagName('main')[0].classList.remove('right') })()">Sign
                            in</button>
                    </div>
                </div>
                </div>
            </main>
        </body>
        </html>
HTML;

        
    }
}



?>