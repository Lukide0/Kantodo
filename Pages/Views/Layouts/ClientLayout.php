<?php

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Base\Layout;

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
    public function Render(string $content = '', array $params = [])
    {
        $headerContent = Application::$APP->header->GetContent();

        // TODO: generovat menu z array
        //$userID = Application::$APP->session->get('user')['id'];
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
                    <ul>
                        <?php 
                        foreach ($params['projects'] ?? [] as $project):
                        ?>
                        <li data-project-id='<?= $project['uuid'] ?>'><a href="/project/<?= base64EncodeUrl($project['uuid']) ?>"><?= $project['name'] ?></a></li>
                        <?php endforeach; ?>
                        <li class="add"><button class="flat no-border info" data-action="project"><span class="icon outline small">add_box</span><?= t('add') ?></button></li>
                    </ul>
                </div>
                <a class="item last" href="/account">
                    <span class="icon outline medium">account_circle</span>
                    <span class="text"><?= t('account') ?></span>
                </a>
                <script>
                    const projects = [];
                    document.querySelectorAll('[data-project-id]').forEach(el => projects.push({ id: el.dataset.projectId, name: el.children[0].textContent}));

                    window.addEventListener('load',
                        function() {
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
                            win.setAction(function(data) {
                                if (!data[0]) 
                                {
                                    win.setNameError('Empty');
                                }
                                
                                let response = Request.Action('/API/create/project', 'POST', {name: data[0]});
                                response.then(res => {
                                    let project = res.data.project;
                                    Kantodo.success(`Created project (${project.uuid})`);

                                    let addProjectItem = btn.parentElement;

                                    let newProject = document.createElement('li');
                                    newProject.dataset['projectId'] = project.uuid;
                                    newProject.innerHTML = `<a href="/project/${project.uuidSafe}">${data[0]}</a>`;
                                    projects.push({id: project.uuid, name: data[0]});

                                    addProjectItem.parentElement.insertBefore(newProject, addProjectItem);
                                    win.clear();
                                    win.hide();

                                    let snackbar = Modal.Snackbar.create('<?= t('project_was_created') ?>', null, 'success');

                                    snackbar.setParent(document.body.querySelector('main'));
                                    snackbar.show({center: true, top: 5}, 4000, true);

                                }).catch(reason => {
                                    console.log(reason);
                                    Kantodo.error(reason);
                                });
                            });
                            btn.addEventListener('click', function(e) {
                                win.show();
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