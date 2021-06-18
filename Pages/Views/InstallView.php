<?php 

namespace Kantodo\Views;

use Kantodo\Core\IView;

class InstallView implements IView
{
    public function Render(array $params = [])
    {
        echo <<<HTML
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
            <script src="Scripts/Components.js"></script>
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
                    <form action="" method="post">
                        <div data-page="1" style="display:block">
                            <h2>Database</h2>
                            <div class="container">
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" name="dbName" required>
                                        <span>DB Name</span>
                                    </label>
                                    <span class="description">Name of database</span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" name="dbHost" required>
                                        <span>DB Host</span>
                                    </label>
                                    <span class="description">Address of database (e.g. localhost, 127.0.0.1)</span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" name="dbUser" required>
                                        <span>DB User</span>
                                    </label>
                                    <span class="description">Database user</span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4 input-open">
                                        <input type="password" name="dbPass" required>
                                        <span>Heslo</span>
                                        <div class="input-close"><span class="material-icons-outlined" data-show="false" onclick="switchPasswordVisibility(event)">visibility</span></div>
                                        <span>DB Password</span>
                                    </label>
                                    <span class="description">Database user password</span>
                                </div>
                                <div class="row main-space-between cross-baseline">
                                    <label class="text info-focus col-4">
                                        <input type="text" value="todo_" name="dbPrefix" required>
                                        <span>DB Prefix</span>
                                    </label>
                                    <span class="description">Database table prefix</span>
                                </div>
                            </div>
                        </div>
                        <div data-page="2" style="display:none">
                            <h2>Admin</h2>
                            <div class="container">
                                <div class="row main-space-between">
                                    <div class="container">
                                        <label class="text info-focus">
                                            <input type="text" name="adminName" required>
                                            <span>Jmeno</span>
                                        </label>
                                        <div class="error-text"></div>
                                    </div>
                                    <div class="container">
                                        <label class="text info-focus">
                                            <input type="text" name="adminSurname" required>
                                            <span>Prijmeni</span>
                                        </label>
                                        <div class="error-text">A</div>
                                    </div>
                                </div>
                                <div class="row main-space-between">
                                    <div class="container">
                                        <label class="text info-focus">
                                            <input type="text" name="adminEmail" required>
                                            <span>Email</span>
                                        </label>
                                        <div class="error-text"></div>
                                    </div>
                                </div>
                                <div class="row main-space-between">
                                    <div class="container">
                                        <label class="text info-focus input-open">
                                            <input type="password" name="adminPass" required>
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
                            </div>
                        </div>
                        <div data-page="3" style="display:none">
                            <h2>Summary</h2>
                            <div class="container">´
                                <button type="submit">Setup</button>
                            </div>
                        </div>
                    </form>
                    <div class="row main-space-around">
                        <button id="previusPageBtn" disabled>Back</button>
                        <button id="nextPageBtn" class="info">Next</button>
                    </div>
                    </div>
            </main>
            <script>
                let previusBtn = document.getElementById("previusPageBtn");
                let nextBtn = document.getElementById("nextPageBtn");
                let contentDiv = document.getElementById("content");

                let pages = contentDiv.querySelectorAll("[data-page]");
                let pagesStatus = document.querySelectorAll("nav [data-page]");

                function setPageStatus(page, color) {
                    pagesStatus[page - 1].style = '--color: ' + color;
                }

                let pageNum = 1;


                nextBtn.onclick = () => 
                {
                    if (pageNum == pages.length - 1) 
                    {
                        nextBtn.disabled = true;
                    }
                    pageNum++;

                    if (pageNum != 1) 
                    {
                        previusBtn.disabled = false;
                    }

                    if (pageNum > 1) 
                    {
                        pages[pageNum - 2].style.display = 'none';

                        removeAttributeToElements("[data-page='" + (pageNum - 1) + "'] input", 'required');

                    }

                    pages[pageNum - 1].style.display = 'block';

                }

                previusBtn.onclick = () => 
                {
                    if (pageNum == 2) 
                    {
                        previusBtn.disabled = true;
                    }
                    pageNum--;

                    if (pageNum < pages.length) 
                    {
                        nextBtn.disabled = false;
                    }

                    pages[pageNum].style.display = 'none';

                    pages[pageNum - 1].style.display = 'block';
                }


            </script>
        </body>
        </html>
HTML;
    }
}


?>