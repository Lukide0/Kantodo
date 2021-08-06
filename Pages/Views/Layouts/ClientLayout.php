<?php 

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Layout;
use Kantodo\Models\TeamModel;

use function Kantodo\Core\base64_encode_url;

class ClientLayout extends Layout
{
    public function Render(string $content = '', array $params = [])
    {
        $headerContent = Application::$APP->header->GetContent();

        $teamModel = new TeamModel();
        $tabs = $params['tabs'] ?? [];

        $userID = Application::$APP->session->get('user')['id'];

        $teams = $teamModel->getUserTeams($userID);

        $path = Application::$URL_PATH;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="<?= $path ?>/styles/main.css">
            <script src="<?= $path ?>/scripts/main.js"></script>
            <script src="<?= $path ?>/scripts/window.js"></script>
            <script src="<?= $path ?>/scripts/animation.js"></script>
            <script src="<?= $path ?>/scripts/request.js"></script>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
            <?= $headerContent ?>
        </head>
        <body class="theme-light">
            <header>
                <div class="row">
                    <h1>Kantodo</h1>
                    <div class="tabs">
                        <div class="tab"><a href="<?= $path ?>">Home page</a></div>
                        <?php foreach ($tabs as $tab) {
                            ?>
                            <div class="tab"><a href="<?= $tab['path'] ?>"><?= $tab['name'] ?></a></div>
                        <?php } ?>
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
                    <?php
                    foreach ($teams as $team) {
                    ?>
                    <a class="team" href="<?= $path ?>/team/<?= base64_encode_url($team['team_id']) ?>">
                        <div class="icon"></div>
                        <div>
                            <div class="name"><?= $team['name'] ?></div>
                            <div class="members-count"><?= $team['members'] ?> members</div>
                        </div>
                    </a>
                    <?php } ?>
                </div>
            </aside>
            <main>
                <?= $content; ?>
            </main>
            <script>
            (function() {
                'use-strict';
                
                let addTeamBtn = document.getElementById('addTeam');
                let content = `
                <div class="container">
                    <div class="container" style="margin-top: 5px">
                        <label>
                            <div class="text-field outline">
                                <input type="text" name="teamName" required>
                                <div class="label">Name</div>
                            </div>
                            <div class="error-msg"></div>
                        </label>
                    </div>
                    <div class="container" style="margin-top: 5px">
                        <label>
                            <div class="text-field outline">
                                <input type="text" name="teamDesc" required>
                                <div class="label">Description</div>
                            </div>
                            <div class="error-msg"></div>
                        </label>
                    </div>
                    <div class='row main-center' style="margin-top: 5px">
                        <button class='long primary'>Create</button>
                    <div>
                </div>
                `;
                let formWindow, btn;
                
                createFormWindow();

                addTeamBtn.onclick = function() 
                {
                    if (!formWindow.isOpened)
                        formWindow.show()
                }

                function createFormWindow() 
                {
                    formWindow = Window('Create team', content);
                    formWindow.setMove()
                    formWindow.setClose()
                    formWindow.onDestroy = createFormWindow;
                    formWindow.onShow = function() {
                        let inputs = formWindow.$("input");
                        inputs.forEach(el => {
                            el.addEventListener("change", function() {
                                if (el.value == "")
                                    el.parentElement.classList.remove("focus");
                                else
                                    el.parentElement.classList.add("focus");
                            });
                        });
                    }


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
                        

                        const request = Request('<?= Application::$URL_PATH ?>/create/team', 'POST', params);
                        request.then(result => {
                            let res = JSON.parse(result);
                            if (res.data == true) 
                            {
                                // TODO ADD TEAM TO LIST

                                formWindow.onClose = function() 
                                {
                                    let inputs = formWindow.$("input");
                                    inputs.forEach(el => {
                                        el.value = "";
                                    });
                                }
                                formWindow.close();
                            }
                        });
                    }
                }
            })();
            </script>
        </body>
        </html>
        <?php

    }
}



?>