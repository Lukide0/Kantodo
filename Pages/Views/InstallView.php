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
            <title>Install</title>
        </head>
        <body>
            <header>
                <h1>Kantodo</h1>
            </header>
            <nav>
                <div>
                    <span class="material-icons-outlined">storage</span>
                </div>
                <div>
                    <span class="material-icons-round">person</span>
                </div>
                <div>
                    <span class="material-icons-round">settings</span>
                </div>
            </nav>
            <main>
                <div id="content">
                    <h2>Database</h2>
                    <div class="container">
                        <div class="row main-space-between cross-baseline">
                            <label class="text col-4">
                                <input type="text" required>
                                <span>DB Name</span>
                            </label>
                            <span class="description">Name of database</span>
                        </div>
                        <div class="row main-space-between cross-baseline">
                            <label class="text col-4">
                                <input type="text" required>
                                <span>DB Host</span>
                            </label>
                            <span class="description">Address of database (e.g. localhost, 127.0.0.1)</span>
                        </div>
                        <div class="row main-space-between cross-baseline">
                            <label class="text col-4">
                                <input type="text" required>
                                <span>DB User</span>
                            </label>
                            <span class="description">Database user</span>
                        </div>
                        <div class="row main-space-between cross-baseline">
                            <label class="text col-4">
                                <input type="password" required>
                                <span>DB Password</span>
                            </label>
                            <span class="description">Database user password</span>
                        </div>
                        <div class="row main-space-between cross-baseline">
                            <label class="text col-4">
                                <input type="text" value="todo_" required>
                                <span>DB Prefix</span>
                            </label>
                            <span class="description">Database table prefix</span>
                        </div>
                    </div>
                    <div class="row main-space-around">
                        <button disabled>Back</button>
                        <button class="info">Next</button>
                    </div>
                </div>
            </main>
        </body>
        </html>
        HTML;
    }
}


?>