<?php 

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Layout;
use Kantodo\Models\TeamModel;

class ClientLayout extends Layout
{
    public function Render(string $content = '', array $params = [])
    {
        $headerContent = Application::$APP->header->GetContent();

        $teamModel = new TeamModel();
        $teams = $teamModel->getUserTeams();
        // todo
        // teams from database

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="Styles/style.css">
            <link rel="stylesheet" href="Styles/flex.css">
            <script src="Scripts/Window.js"></script>
            <script src="Scripts/Animation.js"></script>
            <script src="Scripts/Request.js"></script>
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
            <script>
            (function() {
                'use-strict'
                
                let addTeamBtn = document.getElementById('addTeam');
                let content = `
                <div class="container">
                    <div class="container" style="margin-top: var(--gap-medium)">
                        <label class="text info-focus">
                            <input type="text" name="teamName" required>
                            <span>Name</span>
                        </label>
                        <div class="error-text"></div>
                    </div>
                    <div class="container" style="margin-top: var(--gap-medium)">
                        <label class="text info-focus">
                            <input type="text" name="teamDesc" required>
                            <span>Description</span>
                        </label>
                        <div class="error-text"></div>
                    </div>
                    <div class='row main-center' style="margin-top: var(--gap-medium)">
                        <button class='long primary'>Create</button>
                    <div>
                </div>
                `;
                let formWindow, btn;
                
                create();

                addTeamBtn.onclick = function() 
                {
                    if (!formWindow.isOpened)
                        formWindow.show()
                }

                function create() 
                {
                    formWindow = Window('Create team', content);
                    formWindow.setMove()
                    formWindow.setClose()
                    formWindow.onDestroy = create;
                    btn = formWindow.$('button')[0];
                    btn.onclick = function() 
                    {
                        let inputs = formWindow.$('input');
                        let params = {};
                        let error = false;
                        for (let i = 0; i < inputs.length; i++) {
                            const element = inputs[i];

                            let parent = element.parentNode;

                            if (element.value == '')
                            {
                                parent.classList.add('error');
                                parent.parentNode.children[1].innerText = 'Empty';
                                error = true;
                            } else 
                            {
                                parent.classList.remove('error');
                                parent.parentNode.children[1].innerText = '';
                            }
                            params[element.name] = element.value;
                        }

                        if (error) 
                            return;

                        console.log(params);
                        

                        const request = Request('<?= Application::$URL_PATH ?>/create/team', 'POST', params);
                        console.log(request)
                    }
                }


            })()
            </script>
        </body>
        </html>
        <?php

    }
}



?>