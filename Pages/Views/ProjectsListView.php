<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

/**
 * Projekty
 */
class ProjectsListView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->registerStyle('/styles/projects.css');

        $projects  = $params['projects'] ?? [];
        $uuid      = $params['uuid'];
        $rawTeamID = base64_encode($params['teamID']);

        $abspath = Application::$URL_PATH;

        ?>
        <div class="row main-end">
            <button class="primary" data-team='<?=$uuid;?>'>Add</button>
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

        foreach ($projects as $project) {
            $projID       = base64_encode($project['project_id']);
            $completed    = $project['task_completed'];
            $notCompleted = $project['task_not_completed'] ?? 0;
            $countOfTasks = ($completed + $notCompleted) || 1;

            $percentageCompleted = ($completed / $countOfTasks) * 100;
            ?>
                        <tr>
                            <td colspan="2" data-project="<?=$projID;?>">
                                <a href="<?=$abspath;?>/team/<?=$rawTeamID;?>/project/<?=$projID;?>">
                                    <h3><?=$project['name'];?></h3>
                                    <div class="progress" data-completed="<?=$percentageCompleted;?>">
                                        <div class="completed"></div>
                                    </div>
                                </a>
                            </td>
                        </tr>
                    <?php }?>
                </tbody>
                <tfoot></tfoot>
            </table>
        </div>

        <script>
            (function() {
                'use-strict';

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

                let uuid = '<?=$uuid;?>';

                let addBtn = document.querySelector(`[data-team='${uuid}']`);

                let formWindow = createFormWindow('Create project', content, '<?=Application::$URL_PATH;?>/team/<?=$rawTeamID;?>/create/project', function(self) {

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
                });

                addBtn.onclick = function() {
                    if (!formWindow.isOpened)
                        formWindow.show()
                }


            })();
        </script>
<?php
    }
}

?>