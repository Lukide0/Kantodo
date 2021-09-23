<?php

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Base\Layout;
use Kantodo\Models\TeamModel;

/**
 * Layout pro uživatele
 */
class ClientLayout extends Layout
{
    /**
     * Render
     *
     * @param   string  $content  kontent
     * @param   array   $params   parametry
     *
     * @return  void
     */
    public function Render(string $content = '', array $params = [])
    {
        $headerContent = Application::$APP->header->GetContent();

        //$teamModel = new TeamModel();
        $tabs      = $params['tabs'] ?? [];

        $userID = Application::$APP->session->get('user')['id'];

        $teams = [];// $teamModel->getUserTeams($userID);

        $path = Application::$URL_PATH;
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Home</title>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="./styles/main.css">
            <?=$headerContent;?>
        </head>
        <body>
            <header>
            <h1>Kantodo</h1>
            <nav>
                <a class="item active">
                    <span class="icon outline medium">dashboard</span>
                    <span class="text">Dashboard</span>
                </a>
                <a class="item">
                    <span class="icon outline medium">event</span>
                    <span class="text">Calendar</span>
                </a>
                <div class="item dropdown expanded">
                    <div>
                        <span class="icon outline medium">folder</span>
                        <span class="text">Projects</span>
                    </div>
                    <ul>
                        <li>KantodoApp</li>
                        <li class="add">Add New</li>
                    </ul>
                </div>
                <a class="item last">
                    <span class="icon outline medium">account_circle</span>
                    <span class="text">Account</span>
                </a>
            </nav>
        </header>
        <main>
            <div class="row h-space-between">
                <h2>My work</h2>
                <div class="row">
                    <div class="button-dropdown">
                        <button class="filled">Add task</button>
                        <button class="dropdown"><span class="icon round">expand_more</span></button>
                    </div>
                    <button class="flat icon outline space-medium-left">settings</button>
                </div>
            </div>
            <div class="row">
                <div class="task-list">
                    <div class="dropdown-header">
                        <h3>KantodoApp</h3>
                        <div class="line"></div>
                        <button class="flat icon round">filter_alt</button>
                    </div>
                    <div class="container">
                        <div class="task">
                            <header>
                                <div>
                                    <label class="checkbox">
                                        <input type="checkbox">
                                        <div class="background"></div>
                                    </label>
                                    <h4>Search inspirations for upcoming project</h4>
                                </div>
                                <div>
                                    <div class="important">Important</div>
                                    <button class="flat no-border icon round">more_vert</button>
                                </div>
                            </header>
                            <main>
                                <p>There is so much great inspiration in this world.</p>
                            </main>
                            <footer>
                                <div class="avatars">
                                    <div class="avatar"></div>
                                </div>
                                <div class="row">
                                    <div class="tags">
                                        <div class="tag">New Client</div>
                                    </div>
                                    <div class="row middle"><span class="space-small-right">3 Comments</span><span class="icon round">chat_bubble</span></div>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
                <div class="milestone-list">
                    <div class="milestone">
                        <div class="date">
                            <span class="month">Sep</span>
                            <span class="day">18</span>
                        </div>
                        <div class="container">
                            <p>Write 15 blog articles on Medium</p>
                            <span class="description">Office/Marketing</span>
                            <div class="progress-bar">
                                <div class="bar">
                                    <div class="completed" style="width: 72%;"></div>
                                </div>
                                <span>72% Completed</span>
                            </div>
                        </div>
                    </div>
                    <div class="milestone">
                        <div class="date">
                            <span class="month">Nov</span>
                            <span class="day">02</span>
                        </div>
                        <div class="container">
                            <p>Publish 20 dribbbles</p>
                            <span class="description">Office/Marketing</span>
                            <div class="progress-bar">
                                <div class="bar">
                                    <div class="completed" style="width: 15%;"></div>
                                </div>
                                <span>15% Completed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        </body>

        </html>
<?php

    }
}

?>