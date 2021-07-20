<?php 

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Layout;


class ClientLayout extends Layout
{
    public function Render(string $content = "", array $params = [])
    {
        $headerContent = Application::$APP->header->GetContent();

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="Styles/style.css">
            <link rel="stylesheet" href="Styles/flex.css">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
            <?= $headerContent ?>
        </head>
        <body>
            <header>
                <div class="row">
                    <h1>Kantodo</h1>
                    <div class="tabs">
                        <div class="tab"><p>Projects</p></div>
                        <div class="tab"><p>Calendar</p></div>
                    </div>
                </div>
                <div class="actions">
                    <button class="icon-big flat">
                        <span class="material-icons-outlined">notifications</span>
                    </button>
                    <div class="avatar arrow">
                    </div>
                </div>
            </header>
            <aside>
                <div class="row main-space-between">
                    <h3 id="teams">Teams</h3>
                    <button class="primary icon round floating" id="addTeam">
                        <span class="material-icons-round">
                        add
                        </span>
                    </button>
                </div>
                <div class="teams container">
                    <div class="team">
                        <div class="icon"></div>
                        <div>
                            <div class="name">Managment</div>
                            <div class="members-count">5 members</div>
                        </div>
                    </div>
                    <div class="team">
                        <div class="icon"></div>
                        <div>
                            <div class="name">Managment</div>
                            <div class="members-count">5 members</div>
                        </div>
                    </div>
                </div>
            </aside>
            <main>
                <?= $content; ?>
            </main>
        </body>
        </html>
        <?php

    }
}



?>