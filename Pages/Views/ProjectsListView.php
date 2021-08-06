<?php 

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\IView;

use function Kantodo\Core\base64_encode_url;

class ProjectsListView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->registerStyle('/styles/projects.css');
        
        $projects = $params['projects'] ?? [];
        $uuid = $params['uuid'];
        $rawTeamID = base64_encode_url($params['teamID']);

        $abspath = Application::$URL_PATH;

?>
        <div class="row main-end">
            <button class="primary" data-team='<?= $uuid ?>'>Add</button>
        </div>
        <div class="container">
            <table class="projects col-lg-1 col-md-1">
                <thead>
                    <tr>
                        <td id="status">
                            <div>
                                <div class="active">1 Open (TODO)</div>
                                <div>0 Close (TODO)</div>        
                            </div>
                        </td>
                        <td id="filter">
                            <div>
                                <span class="material-icons-round">
                                filter_alt
                                </span>
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody>
                <?php

                foreach ($projects as $project) 
                {
                    $projID = base64_encode_url($project['project_id']);
                    $completed = $project['task_completed'];
                    $notCompleted = $project['task_not_completed'] ?? 0;
                    $countOfTasks = ($completed + $notCompleted) || 1;

                    $percentageCompleted = ($completed / $countOfTasks) * 100;
                ?>
                    <tr>
                        <td colspan="2" data-project="<?= $projID ?>">
                            <a href="<?= $abspath ?>/team/<?= $rawTeamID ?>/project/<?= $projID ?>">
                                <h3><?= $project['name'] ?></h3>
                                <div class="progress" data-completed="<?= $percentageCompleted ?>">
                                    <div class="completed"></div>
                                </div>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot></tfoot>
            </table>
        </div>

        <script>
        (function() {
            'use-strict';

            let formWindow, btn;

            let content = `
                <div class="container">
                    <div class="container" style="margin-top: 5px">
                        <label>
                            <div class="text-field outline">
                                <input type="text" name="projName" required>
                                <div class="label">Name</div>
                            </div>
                            <div class="error-msg"></div>
                        </label>
                    </div>
                    <div class="container" style="margin-top: 5px">
                        <label>
                            <div class="text-field outline">
                                <input type="text" name="projDesc" required>
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

            let uuid = '<?= $uuid ?>';

            let addBtn = document.querySelector(`[data-team='${uuid}']`);

            createFormWindow();

            addBtn.onclick = function() {
                if (!formWindow.isOpened)
                    formWindow.show()
            }

            function createFormWindow() 
            {
                formWindow = Window('Create project', content);
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
                    

                    const request = Request(`<?= Application::$URL_PATH ?>/team/<?= $rawTeamID ?>/create/project`, 'POST', params);
                    request.then(result => {
                        console.log(result);
                        /*

                        let res = JSON.parse(result);
                        if (res.data == true) 
                        {
                            // TODO ADD PROJECT TO LIST

                            formWindow.onClose = function() 
                            {
                                let inputs = formWindow.$("input");
                                inputs.forEach(el => {
                                    el.value = "";
                                });
                            }
                            formWindow.close();
                        }*/
                    });
                }
            }


        })();


        </script>
        <?php
    }
}

?>