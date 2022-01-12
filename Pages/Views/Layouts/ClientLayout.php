<?php

declare(strict_types = 1);

namespace Kantodo\Views\Layouts;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\Layout;
use Kantodo\Models\ProjectModel;

use function Kantodo\Core\Functions\base64EncodeUrl;
use function Kantodo\Core\Functions\t;

/**
 * Layout pro uživatele
 */
class ClientLayout extends Layout
{
    /**
     * Render
     *
     * @param   string  $content  kontent
     * @param   array<mixed>   $params   parametry
     *
     * @return  void
     */
    public function render(string $content = '', array $params = [])
    {

        // TODO: Active item v menu, odstraneni projektu WS
        $headerContent = Application::$APP->header->getContent();
        if (!isset($params['projects'])) 
        {
            $projectModel = new ProjectModel();

            $user = Auth::getUser();

            if ($user === null) 
            {
                Auth::signOut();
                Application::$APP->response->setLocation('/auth');
                exit;
            }

            $projects = $projectModel->getUserProjects((int)$user['id']);
    
            if ($projects === false)
                $projects = [];
        } else 
        {
            $projects = $params['projects'];
        }

        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/main.min.css">
            <script src="<?= Application::$SCRIPT_URL ?>main.js"></script>
            <script src="<?= Application::$SCRIPT_URL ?>global.js" type="module"></script>
            <?=$headerContent;?>
            <!-- MD editor - START-->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
            <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
            <script src="https://cdn.jsdelivr.net/highlight.js/latest/highlight.min.js"></script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/highlight.js/latest/styles/github.min.css">
            <!-- MD editor - END-->
            <script>
                const translations = {
                    <?php
                        foreach (Application::$APP->lang->getAll('global') as $key => $value) {
                            echo "'%{$key}%': \"$value\",";
                        }
                    ?>
                };

            </script>
        </head>
        <body>
            <header>
            <h1>Kantodo</h1>
            <nav>
                <a class="item active" href="/">
                    <span class="icon outline medium">dashboard</span>
                    <span class="text"><?= t('dashboard') ?></span>
                </a>
                <a class="item" href="/calendar">
                    <span class="icon outline medium">event</span>
                    <span class="text"><?= t('calendar') ?></span>
                </a>
                <div class="item dropdown expanded">
                    <div>
                        <span class="icon outline medium">folder</span>
                        <span class="text"><?= t('projects') ?></span>
                    </div>
                    <div class="row center space-medium-top space-medium-bottom" style="margin-left: auto; margin-right: auto;">
                        <button class="flat no-border info" data-action="project"><span class="icon outline small">add_box</span><?= t('add') ?></button>
                    </div>
                    <ul id="projectList">
                        <?php 
                        foreach ($projects ?? [] as $project):
                            $uuid = base64EncodeUrl($project['uuid']);
                        ?>
                        <li data-project-id='<?= $uuid ?>'><a href="/project/<?= $uuid ?>"><?= $project['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a class="item last" href="/account">
                    <span class="icon outline medium">account_circle</span>
                    <span class="text"><?= t('account') ?></span>
                </a>
                <a class="item" href="/auth/signout">
                    <span class="icon outline medium">logout</span>
                    <span class="text"><?= t('sign_out') ?></span>
                </a>
                <script>

                    var DATA = {
                        "Container": null,
                        "Projects": {},
                        "AddProject": function(uuid, name) 
                        {
                            let container = document.getElementById("projectList");
                            let newProject = document.createElement('li');
                            newProject.dataset['projectId'] = uuid;
                            newProject.innerHTML = `<a href="/project/${uuid}">${name}</a>`;
                            console.log(container);
                            container.insertBefore(newProject, container.lastChild);

                            let tmp = document.createElement('div');
                            tmp.innerHTML = `
                                <div class="project" data-project-id="${uuid}" data-loaded="false" data-last="0">
                                    <div class="dropdown-header">
                                        <h3>${name}</h3>
                                        <div class="line"></div>
                                    </div>
                                    <div class="container">
                                        <button onclick="loadNext(event)" class="hover-shadow flat no-border" style="margin: 10px auto"><?= t('load') ?></button>
                                    </div>
                                </div>`
                            this.Container.append(tmp.children[0]);                            
                            this.Projects[uuid] = ({name: name, tasks: []});
                        },
                    };                    
                    
                    document.querySelectorAll('[data-project-id]').forEach(el => DATA.Projects[el.dataset.projectId] = {name: el.children[0].textContent, tasks: []});

                    window.addEventListener('load',
                    function() {
                            DATA.Container = document.querySelector('.task-list');
                            let btn = document.querySelector("button[data-action=project]");
                            let win = Modal.ModalProject.create();

                            win.setParent(document.body.querySelector('main'));

                            win.setNameValidation(function(e, el){
                                if (!el.value) {
                                    win.setNameError('Empty');
                                    return false;
                                } else {
                                    win.clearNameError();
                                    return true;
                                }
                            });
                            win.setActionCreate(function(data) {
                                if (!data[0]) 
                                {
                                    win.setNameError('Empty');
                                    return;
                                }
                                let response = Request.Action('/api/create/project', 'POST', {name: data[0]});
                                response.then(res => {
                                    let project = res.data.project;
                                    Kantodo.success(`Created project (${project.uuid})`);


                                    win.clear();
                                    win.hide();
                                    
                                    let snackbar = Modal.Snackbar.create('<?= t('project_was_created') ?>', null, 'success');
                                    snackbar.show();
                                    
                                    DATA.AddProject(project.uuid, data[0]);

                                }).catch(reason => {
                                    let snackbar = Modal.Snackbar.create(reason.statusText, null, 'error');
                                    snackbar.show();
                                    win.hide(true);
                                    Kantodo.error(reason);
                                });
                            });


                            btn.addEventListener('click', function(e) {
                                win.show();
                            });

                            
                            win.setActionJoin(function(data) {
                                if (!data[0]) 
                                {
                                    win.setCodeError('Empty');
                                    return;
                                }
                                win.clearCodeError();

                                let response = Request.Action('/api/join/project', 'POST', {code: data[0]});
                                response.then(res => {
                                    console.log(res);
                                    let project = res.data.project;
                                    Kantodo.success(`Join project (${project.uuid})`);

                                    DATA.AddProject(project.uuid, project.name);
                                    win.clear();
                                    win.hide();

                                    let snackbar = Modal.Snackbar.create('<?= t('you_have_joined_project') ?>', null, 'success');
                                    snackbar.show();

                                }).catch(reason => {
                                    Kantodo.error(reason);
                                });
                            });
                        }
                    );
                </script>
            </nav>
        </header>
        <main>
            <?= $content ?>
        </main>
        </body>

        </html>
<?php
    }
}

?>