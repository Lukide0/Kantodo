<?php

namespace Kantodo\Views\Layouts;

use function Kantodo\Core\Functions\base64_encode_url;
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

        $teamModel = new TeamModel();
        $tabs      = $params['tabs'] ?? [];

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
            <link rel="stylesheet" href="<?=$path;?>/styles/main.css">
            <script src="<?=$path;?>/scripts/main.js"></script>
            <script src="<?=$path;?>/scripts/window.js"></script>
            <script src="<?=$path;?>/scripts/animation.js"></script>
            <script src="<?=$path;?>/scripts/request.js"></script>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
            <?=$headerContent;?>
        </head>

        <body class="theme-light">
            <header>
                <div class="row">
                    <h1>Kantodo</h1>
                    <div class="tabs">
                        <div class="tab"><a href="<?=$path;?>">Home page</a></div>
                        <?php foreach ($tabs as $tab) {
            ?>
                            <div class="tab"><a href="<?=$tab['path'];?>"><?=$tab['name'];?></a></div>
                        <?php }?>
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
                        <a class="team" href="<?=$path;?>/team/<?=base64_encode($team['team_id']);?>">
                            <div class="icon"></div>
                            <div>
                                <div class="name"><?=$team['name'];?></div>
                                <div class="members-count"><?=$team['members'];?> members</div>
                            </div>
                        </a>
                    <?php }?>
                </div>
            </aside>
            <main>
                <?=$content;?>
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
                    const formWindow = createFormWindow('Create team', content, '<?=Application::$URL_PATH;?>/create/team', function(self) {

                    let btn = self.$('button')[0];
                    btn.onclick = function() {
                        let inputs = self.$('input');
                        let params = {};
                        let error = false;
                        for (let i = 0; i < inputs.length; i++) {
                            const element = inputs[i];

                            let parent = element.parentNode;

                            if (element.value == '') {
                                parent.classList.add('error');
                                parent.parentNode.children[1].innerText = 'Empty';
                                error = true;
                            } else {
                                parent.classList.remove('error');
                                parent.parentNode.children[1].innerText = '';
                            }
                            params[element.name] = element.value;
                        }

                        if (error)
                            return;


                        const request = self.request(params);
                        request.then(result => {
                            let res = JSON.parse(result);
                            if (res.data == true) {
                                // TODO ADD TEAM TO LIST

                                self.onClose = function() {
                                    let inputs = self.$("input");
                                    inputs.forEach(el => {
                                        el.value = "";
                                    });
                                }
                                self.close();
                            }
                        });
                    };
                });

                addTeamBtn.onclick = function() {
                    if (!formWindow.isOpened)
                        formWindow.show();
                };
            })();
            </script>
        </body>

        </html>
<?php

    }
}

?>