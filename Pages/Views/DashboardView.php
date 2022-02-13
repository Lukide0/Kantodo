<?php

declare(strict_types = 1);

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Widgets\Task;

use function Kantodo\Core\Functions\base64EncodeUrl;
use function Kantodo\Core\Functions\t;

/**
 * Hlavní stránka
 */
class DashboardView implements IView
{
    /**
     * Homepage
     *
     * @param   array<mixed>  $params
     *
     * @return  void
     */
    public function render(array $params = [])
    {
        Application::$APP->header->setTitle("Kantodo - Dashboard");
        ?>
        <div class="row h-space-between">
            <h2><?= t('my_work', 'dashboard') ?></h2>
            <div class="row">
                <button class="hover-shadow flat no-border space-medium-right" onclick="showCompletedTasks(event)"><?= t("show_completed_tasks")?></button>
                <button data-action='task' class="filled big hover-shadow"><?= t('add_task', 'dashboard') ?></button>
                <script>
                DATA.AfterProjectAdd = function(uuid, name) 
                {
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
                    loadTasks(tmp.querySelector('h3'));
                    document.querySelector('.task-list').append(tmp.children[0]);
                }


                window.addEventListener('load', function(){
                    let btn = document.querySelector('button[data-action=task]');
                    taskWin.setButtonShow(btn);

                    btn.addEventListener('click', function() {  taskWin.actionCreate();  });


                    taskWin.setActionCreate(createTask);
                    // TODO: client data valid ex. empty project
                    function createTask() {
                        let inputName = taskWin.getNameInput();
                        let data = {};

                        data.task_name = inputName.value;
                        data.task_desc = taskWin.getEditor().value();
                        data.task_proj = taskWin.getProjectInput().dataset.value;
                        data.task_comp = taskWin.getStatus();
                        data.task_priority = taskWin.getPriority();
                        data.task_end_date = taskWin.getEndDate();

                        let chipsArray = taskWin.getChips();
                        for (let i = 0; i < chipsArray.length; i++) {
                            data[`task_tags[${i}]`] = chipsArray[i];
                        }
                        if (!data.task_proj)
                            return;

                        let projectEl = document.querySelector(`main [data-project-id=${data.task_proj}] > .container`);
                        let taskData = {
                            completed: data.task_comp,
                            description: data.task_desc,
                            tags: chipsArray,
                            name: data.task_name,
                            priority: data.task_priority,
                            end_date: data.task_end_date,
                        };

                        let response = Request.Action('/api/create/task', 'POST', data);
                        response.then(res => {
                            let task = res.data.task;
                            Kantodo.success(`Created task (${task.id})`);
                            taskData.id = task.id.toString();
                            DATA.AddTask(data.task_proj, taskData, projectEl);

                            taskWin.clear();

                            taskWin.hide();

                            let snackbar = Modal.Snackbar.create('<?= t('task_was_created') ?>', null ,'success');
                            snackbar.show();
                            projectEl.dataset['last'] = task.id;

                        }).catch(reason => {

                            let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');
                            snackbar.show();
                            Kantodo.error(reason);
                        }).finally(() => {
                            taskWin.hide();
                        });
                    };
                });
                </script>
            </div>
        </div>
        <div class="row nowrap">
            <div class="task-list">
                <?php foreach ($params['projects'] as $project) :?>
                <div class="project" data-project-id='<?= base64EncodeUrl($project['uuid']) ?>'>
                    <div class="dropdown-header">
                        <h3><?= $project['name']?></h3>
                        <div class="line"></div>
                    </div>
                    <div class="container">
                        <button onclick="loadNext(event)" class="hover-shadow flat no-border" style="margin: 10px auto"><?= t('load') ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <script>
                function loadTasks(el) 
                {
                    el.addEventListener('click', function(e) {
                        if (el.parentElement.parentElement.classList.contains('expanded'))
                            el.parentElement.parentElement.classList.remove('expanded');
                        else {
                            let projectEl = el.parentElement.parentElement;
                            projectEl.classList.add('expanded');
                            
                            if (projectEl.dataset.loaded)
                                return;

                            projectEl.dataset.loaded = true;

                            let response = Request.Action('/api/get/task/' + projectEl.dataset.projectId, 'GET');
                            response.then(res => {
                                let tasks = res.data.tasks;
                                tasks.forEach(task => {
                                    DATA.AddTask(projectEl.dataset.projectId, task, projectEl.querySelector('.container'));
                                });

                                if (tasks.length > 0)
                                    projectEl.dataset['last'] = tasks[tasks.length - 1].id;
                            });
                        }
                    });
                }

                document.querySelectorAll('.task-list > .project > .dropdown-header > h3').forEach(el => loadTasks(el));

                function loadNext(e) {
                    let el = e.target;
                    el.classList.add('disabled');
                    let projectEl = el.parentNode.parentNode;
            
                    loadProjectTasks(
                        projectEl.dataset.projectId, 
                        projectEl.dataset['last'],
                        function(tasks) { 
                            if (tasks.length > 0) 
                            {
                                projectEl.dataset['last'] = tasks[tasks.length - 1].id;
                            }
                            tasks.forEach(task => {
                                DATA.AddTask(projectEl.dataset.projectId, task, projectEl.querySelector('.container'));
                            });
                        },
                        function() 
                        {
                            el.classList.remove('disabled');
                        }
                    );
                }
            </script>
        </div>
        <script src="<?= Application::$SCRIPT_URL?>task.js"></script>
            
<?php
}
}

?>