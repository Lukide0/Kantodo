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
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/markdown.css">
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
                var taskWin;

                // TODO: check if cookie expire Inteterval 10s, ws odstraneni projektu z menu
            </script>
        </head>
        <body>
            <header>
            <h1>Kantodo</h1>
            <nav>
                <a class="item" href="/">
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
                    <ul id="projectList" style="max-height: 350px;">
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
                        "Projects": {},
                        "AddProject": function(uuid, name) 
                        {
                            let container = document.getElementById("projectList");
                            let newProject = document.createElement('li');
                            newProject.dataset['projectId'] = uuid;
                            newProject.innerHTML = `<a href="/project/${uuid}">${name}</a>`;
                            container.insertBefore(newProject, container.lastChild);
                            this.Projects[uuid] = ({name: name, tasks: []});

                            if (this.AfterProjectAdd != null)
                                this.AfterProjectAdd(uuid, name);
                        },
                        "AddTask": function(uuid, task, meta) 
                        {
                            if (typeof this.Projects[uuid] !== "object") 
                            {
                                return;
                            }

                            // úkol již existuje
                            if (this.Projects[uuid].tasks.some(t => t.id == task.id)) 
                            {
                                return;
                            }

                            this.Projects[uuid].tasks.push(task);
                            if (this.AfterTaskAdd != null)
                                this.AfterTaskAdd(uuid, task, meta);
                        },
                        "UpdateTask": function(uuid, data) 
                        {
                            if (typeof this.Projects[uuid] !== "object") 
                            {
                                return;
                            }

                            let index = this.Projects[uuid].tasks.findIndex((t) => t.id == data.id);
                            
                            if (index == -1) return;


                            this.Projects[uuid].tasks[index] = data;
                            
                            if (this.AfterTaskUpdate != null)
                                this.AfterTaskUpdate(uuid, index, data);
                            
                        },
                        "RemoveTask": function(uuid, taskID) 
                        {
                            if (typeof this.Projects[uuid] !== "object") 
                            {
                                return;
                            }

                            let index = this.Projects[uuid].tasks.findIndex((t) => t.id == taskID);
                            if (index == -1) return;
                            
                            this.Projects[uuid].tasks.splice(index, 1);
                            
                            if (this.AfterTaskRemove != null)
                                this.AfterTaskRemove(uuid, taskID);
                        },
                        "AfterTaskUpdate": null, 
                        "AfterProjectAdd": null,
                        "AfterTaskAdd": null,
                        "AfterTaskRemove": null,
                    };                    
                    
                    document.querySelectorAll('[data-project-id]').forEach(el => DATA.Projects[el.dataset.projectId] = {name: el.children[0].textContent, tasks: []});

                    window.addEventListener('load',
                    function() {
                        taskWin = Modal.createTaskWindow();

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
                                
                                let snackbar = Modal.Snackbar.create('<?= t('project_was_created') ?>', null, 'success');
                                snackbar.show();
                                
                                DATA.AddProject(project.uuid, data[0]);

                            }).catch(reason => {
                                let snackbar = Modal.Snackbar.create(reason.statusText, null, 'error');
                                snackbar.show();
                                Kantodo.error(reason);
                            }).finally(() => {
                                win.hide();
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
                                let project = res.data.project;
                                Kantodo.success(`Join project (${project.uuid})`);

                                DATA.AddProject(project.uuid, project.name);
                                win.clear();
                                
                                let snackbar = Modal.Snackbar.create('<?= t('you_have_joined_project') ?>', null, 'success');
                                snackbar.show();
                                
                            }).catch(reason => {
                                Kantodo.error(reason);
                            }).finally(() => {
                                win.hide();
                            });
                        });
                    });
                </script>
            </nav>
        </header>
        <main>
            <?= $content ?>
        </main>
        <script>

            var url;
            if (location.protocol == 'https:')
                url = "wss://";
            else
                url = "ws://";
            
            url += "127.0.0.1:8443";
            const ws = new WebSocket(url, ['access_token','<?= Auth::$PASETO_RAW ?>']);

            let dataFormat = function(action, value) 
            {
                return JSON.stringify({ action, value})
            };

            ws.onopen = function() 
            {
                for (let proj in DATA.Projects) 
                {
                    ws.send(dataFormat('join', proj));
                }

            };

            ws.onmessage = function(msg) 
            {
                console.log(msg.data);
            };

            ws.onclose = function() 
            {
                Kantodo.info("WS connection closing");
            }

        </script>
        </body>

        </html>
<?php
    }
}

?>