<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

/**
 * Instalace
 */
class InstallView implements IView
{
    public function render(array $params = [])
    {
        $lang = Application::$APP->lang;
        $lang->load('install');

        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="Styles/flex.css">
            <link rel="stylesheet" href="Styles/style.css">
            <link rel="stylesheet" href="Styles/install.css">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
            <script src="scripts/components.js"></script>
            <script src="scripts/request.js"></script>
            <script src="scripts/validation.js"></script>
            <title>Install</title>
        </head>

        <body>
            <header>
                <h1>Kantodo</h1>
            </header>
            <nav>
                <div data-page="1">
                    <span class="material-icons-outlined">storage</span>
                </div>
                <div data-page="2">
                    <span class="material-icons-round">person</span>
                </div>
                <div data-page="3">
                    <span class="material-icons-round">settings</span>
                </div>
            </nav>
            <main>
                <div id="content">
                    <form onsubmit="formSubmit(event)">
                        <div data-page="1" style="display:block">
                            <h2>Database</h2>
                            <div class="container">
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" name="dbName" required>
                                        <span>DB Name</span>
                                    </label>
                                    <span class="description"><?=$lang->get('database-name-desc', 'install');?></span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" name="dbHost" required>
                                        <span>DB Host</span>
                                    </label>
                                    <span class="description"><?=$lang->get('database-host-desc', 'install');?></span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" name="dbUser" required>
                                        <span>DB User</span>
                                    </label>
                                    <span class="description"><?=$lang->get('database-user-desc', 'install');?></span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4 input-open">
                                        <input type="password" name="dbPass" required>
                                        <span>Heslo</span>
                                        <div class="input-close"><span class="material-icons-outlined" data-show="false" onclick="switchPasswordVisibility(event)">visibility</span></div>
                                        <span>DB Password</span>
                                    </label>
                                    <span class="description"><?=$lang->get('database-pass-desc', 'install');?></span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" value="todo_" name="dbPrefix" required>
                                        <span>DB Prefix</span>
                                    </label>
                                    <span class="description"><?=$lang->get('database-prefix-desc', 'install');?></span>
                                </div>
                            </div>
                        </div>
                        <div data-page="2" style="display:none">
                            <h2>Admin</h2>
                            <div class="container">
                                <div class="row">
                                    <div class="container">
                                        <label class="text info-focus">
                                            <input type="text" name="adminName" required>
                                            <span><?=$lang->get('first-name');?></span>
                                        </label>
                                        <div class="error-text"></div>
                                    </div>
                                    <div class="container" style="margin-left: var(--gap-huge)">
                                        <label class="text info-focus">
                                            <input type="text" name="adminSurname" required>
                                            <span><?=$lang->get('last-name');?></span>
                                        </label>
                                        <div class="error-text"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="container">
                                        <label class="text info-focus">
                                            <input type="text" name="adminEmail" required>
                                            <span>Email</span>
                                        </label>
                                        <div class="error-text"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="container">
                                        <label class="text info-focus input-open">
                                            <input type="password" name="adminPass" data-password-validation="passwordValidation" required>
                                            <span><?=$lang->get('password');?></span>
                                            <div class="input-close"><span class="material-icons-outlined" data-show="false" onclick="switchPasswordVisibility(event)">visibility</span></div>
                                        </label>
                                        <div class="error-text"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <ul class="requirements" data-password-requirements='adminPass'>
                                        <li data-error='MIN_LENGTH'>At least 8 characters</li>
                                        <li data-error='LOWERCASE_CHAR_COUNT'>One lowercase character</li>
                                        <li data-error='UPPERCASE_CHAR_COUNT'>One uppercase character</li>
                                        <li data-error='NUMBERS_COUNT'>One number</li>
                                        <li data-error='SPECIAL_CHARS_COUNT'>One special character</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div data-page="3" style="display:none">
                            <h2>Summary</h2>
                            <div class="container">
                                <button type="submit">Setup</button>
                            </div>
                        </div>
                    </form>
                    <div class="row main-space-around">
                        <button id="previusPageBtn" disabled><?=$lang->get('back');?></button>
                        <button id="nextPageBtn" class="info"><?=$lang->get('next');?></button>
                    </div>
                </div>
            </main>
            <script>
                let previusBtn = document.getElementById('previusPageBtn');
                let nextBtn = document.getElementById('nextPageBtn');
                let contentDiv = document.getElementById('content');

                let pages = contentDiv.querySelectorAll('[data-page]');
                let pagesStatus = document.querySelectorAll('nav [data-page]');

                function setPageStatus(page, color) {
                    pagesStatus[page - 1].style = '--color: ' + color;
                }

                let pageNum = 1;


                nextBtn.onclick = () => {
                    if (pageNum == pages.length - 1) {
                        nextBtn.disabled = true;
                    }
                    pageNum++;

                    if (pageNum != 1) {
                        previusBtn.disabled = false;
                    }

                    if (pageNum > 1) {
                        pages[pageNum - 2].style.display = 'none';

                        removeAttributeToElements("[data-page='" + (pageNum - 1) + "'] input", 'required');

                    }

                    pages[pageNum - 1].style.display = 'block';

                }

                previusBtn.onclick = () => {
                    if (pageNum == 2) {
                        previusBtn.disabled = true;
                    }
                    pageNum--;

                    if (pageNum < pages.length) {
                        nextBtn.disabled = false;
                    }

                    pages[pageNum].style.display = 'none';

                    pages[pageNum - 1].style.display = 'block';
                }

                function formSubmit(e) {
                    e.preventDefault();
                    let obj = {};
                    for (let index = 0; index < e.target.elements.length; index++) {
                        const element = e.target.elements[index];

                        if (element.name.length != 0)
                            obj[element.name] = element.value;
                    }
                    const request = Request(window.location, 'POST', obj);
                    request.then(response => {
                        console.log(response)
                        if (response.status)
                            location.reload();
                    });
                }

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
                        return;

                    errors.forEach(error => {
                        if (obj.hasOwnProperty(error)) {
                            let el = obj[error];

                            el.classList.add('error');
                        }
                    });
                }
            </script>
        </body>

        </html>
<?php
}
}

?>