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
                <button data-action='task' class="filled big hover-shadow"><?= t('add_task', 'dashboard') ?></button>
                <script>
                let taskWin;
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

                DATA.AfterTaskAdd = function(uuid, task, container) {
                    let tags = task.tags;
                    let tagsHTML = tags.map(tag => {
                        return `<div class="tag">${tag}</div>`;
                    }).join('');

                    let taskEl = document.createElement('div');
                    taskEl.classList.add('task');
                    taskEl.dataset['taskId'] = task.id;
                    taskEl.innerHTML = `
                    <header>
                        <div>
                            <h4>${task.name}</h4>
                        </div>
                        <div>
                            <button class="flat no-border icon round" onclick="showTaskContextMenu(event, '${uuid}', ${task.id});">more_vert</button>
                        </div>
                    </header>
                    <footer>
                        <div class="row">
                            <div class="tags">
                                ${tagsHTML}
                            </div>
                        </div>
                    </footer>`;
                    container.appendChild(taskEl);
                    taskEl.addEventListener('click', function(e) {
                        let taskID = this.dataset.taskId;
                        let taskInfo = DATA.Projects[uuid].tasks.find(t => t.id == taskID);

                        let md = SimpleMDE.prototype.markdown(taskInfo.description);
                        let taskTags = taskInfo.tags.map(tag => {
                            return `<div class="tag space-small-right">${tag}</div>`;
                        }).join('');

                        let priority;
                        switch(taskInfo.priority) 
                        {
                        case '1':
                            priority = translations['%priority_medium%'];
                            break;
                        case '2':
                            priority = translations['%priority_high%'];
                            break;
                        default:
                            priority = translations['%priority_low%'];
                            break;
                        }

                        let icon = (taskInfo.completed == "0") ? "lock_open" : "lock";

                        let desc = 
                        `
                        <div class='container'>
                            <div class="row space-regular-top space-big-bottom">
                                <div class='space-small-right'>${translations['%priority%']}: ${priority}</div>
                                <div class="row">
                                    ${taskTags}
                                </div>    
                            </div>
                            <div class="markdown-body">
                                ${md}
                            </div>
                        </div>
                        `;

                        let title = `<span>${taskInfo.name}</span><span class="icon medium round">${icon}</span>`;

                        let taskDialog = Modal.Dialog.create(title, desc, [
                            {
                                'text': '<?= t("close") ?>', 
                                'classList': 'flat no-border',
                                'click': function(dialogOBJ) {

                                    self.value = self.oldVal;
                                    dialogOBJ.destroy(true);

                                    return false;
                                }
                            }
                        ]);

                        taskDialog.element.children[0].style.minWidth = "75%";
                        taskDialog.setParent(document.body.querySelector('main'));
                        taskDialog.show();
                    });

                };

                ///////////////
                // TASK MENU //
                ///////////////
                let menu;
                function showTaskContextMenu(e,uuid,taskID) {
                    if (menu)
                        menu.element.remove();
                    let {x, y} = e;
                    
                    menu = Dropdown.Menu.create();
                    let taskEl = document.querySelector(`[data-task-id='${taskID}']`);
                    let taskInfo = DATA.Projects[uuid].tasks.find(t => t.id == taskID);

                    let itemEdit = Dropdown.Item.create(translations['%edit%'], null, {'text': 'edit'});
                    itemEdit.element.style = "color: rgb(var(--info-dark)) !important";
                    itemEdit.element.onclick = function() {

                        menu.element.blur();

                        with (taskWin) 
                        {
                            actionUpdate();

                            show();

                            // Název
                            let tmp = getNameInput();
                            tmp.value = taskInfo.name;
                            tmp.parentElement.classList.add('focus');

                            // Popisek
                            getEditor().value(taskInfo.description);

                            // Projekt
                            setProject(DATA.Projects[uuid].name, uuid);

                            // Status
                            setStatus(taskInfo.completed);

                            // Priorita
                            setPriority(taskInfo.priority);

                            // Konec
                            if (taskInfo.end_date != null)
                                setEndDate(new Date(taskInfo.end_date));

                            // Tagy
                            setChips(taskInfo.tags);
                        }

                        taskWin.setActionUpdate(updateTask);

                        function updateTask() {
                            let inputName = taskWin.getNameInput();
                            let chipsArray = taskWin.getChips();

                            let data = {
                                task_id: taskInfo.id,
                                task_proj: taskWin.getProjectInput().dataset.value
                            }
                            let taskData = {
                                completed: taskWin.getStatus(),
                                description: taskWin.getEditor().value(),
                                tags: chipsArray,
                                name: inputName.value,
                                priority: taskWin.getPriority(),
                                end_date: data.task_end_date,
                            };
                            
                            let date = taskWin.getEndDate();
                            if (date)
                                taskData.end_date = new Date(date).toISOString().substr(0, 16);

                            for (let i = 0; i < chipsArray.length; i++) {
                                data[`task_tags[${i}]`] = chipsArray[i];
                            }

                            let projectEl = document.querySelector(`main [data-project-id=${data.task_proj}] > .container`);
                            
                            // zmeny
                            if (taskData.completed != taskInfo.completed)
                                data.task_comp = taskData.completed;
                            
                            if (taskData.description != taskInfo.description)
                                data.task_desc = taskData.description;
                            
                            if (taskData.name != taskInfo.name)
                                data.task_name = taskData.name;

                            if (taskData.priority != taskInfo.priority)
                                data.task_priority = taskData.priority;

                            if (taskData.end_date != taskInfo.end_date)
                                data.task_end_date = taskData.end_date;
                            
                            let response = Request.Action('/api/update/task', 'POST', data);
                            response.then(res => {
                                if (taskData.name != taskInfo.name) 
                                {
                                    projectEl.querySelector(`[data-task-id="${taskInfo.id}"] > header h4`).innerText = taskData.name;
                                }

                                for(var p in taskData)
                                {
                                    taskInfo[p] = taskData[p];
                                }
                            }).catch(reason => {
                                let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');
                                snackbar.show();
                                Kantodo.error(reason);
                            }).finally(() => {
                                taskWin.hide();
                            });
                        }
                    }

                    let itemRemove = Dropdown.Item.create(translations['%remove%'], null, {'text': 'delete'});
                    itemRemove.element.style = "color: rgb(var(--error)) !important";
                    itemRemove.element.onclick = function() {

                        let dialog = Modal.Dialog.create('<?= t("confirm") ?>', `<?= t("do_you_want_delete_this_task")?>`, [{
                                'text': '<?= t("close") ?>', 
                                'classList': 'flat no-border',
                                'click': function(dialogOBJ) {
                                    dialogOBJ.destroy(true);
                                    return false;
                                }
                            }, {
                                'text': '<?= t("yes") ?>',
                                'classList': 'space-big-left text error',
                                'click': deleteTask
                            }
                        ]);
                        dialog.setParent(document.body);
                        menu.element.blur();
                        dialog.show();

                        function deleteTask(dialogOBJ, e) {
                            let data = {
                                'task_id': taskInfo.id,
                                'task_proj': uuid
                            }
                            e.target.classList.add('disabled');

                            let response = Request.Action('/api/remove/task', 'POST', data);
                            response.then(res => {
                                
                                DATA.Projects[uuid].tasks.splice(DATA.Projects[uuid].tasks.findIndex((t) => t.id == taskID), 1);
                                taskEl.remove();

                                let snackbar = Modal.Snackbar.create('<?= t('task_was_removed') ?>', null ,'success');
                                snackbar.show();

                            }).catch(reason => {
                                let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');
                                snackbar.show();

                                Kantodo.error(reason);
                            }).finally(() => {
                                e.target.classList.remove('disabled');
                                dialogOBJ.destroy(true);
                            });
                        }
                    }

                    let itemChangeStatus;

                    if (taskInfo.completed == 0) 
                    {
                        itemChangeStatus = Dropdown.Item.create(translations['%mark_as_completed%'], null, {'text': 'done'});
                    } else {
                        itemChangeStatus = Dropdown.Item.create(translations['%mark_as_incomplete%'], null, {'text': 'close'});
                        itemChangeStatus.element.style = "color: rgb(var(--success-dark)) !important";
                    }


                    menu.items.push(itemChangeStatus, itemEdit, itemRemove);
                    menu.render();

                    document.body.append(menu.element);

                    menu.element.setAttribute("tabindex", -1);
                    menu.element.focus();

                    menu.element.addEventListener('blur', function() {
                        menu.element.remove();
                        menu = null;
                    });

                    let width = menu.element.offsetWidth;
                    let height = menu.element.offsetHeight;

                    if (y + height > window.innerHeight) 
                    {
                        menu.move(x - width, y - height);
                        
                    } else
                    {
                        menu.move(x - width, y);
                    }


                    e.stopPropagation();
                }

                window.addEventListener('load', function(){
                    let btn = document.querySelector('button[data-action=task]');
                    taskWin = Modal.createTaskWindow(btn);

                    btn.addEventListener('click', function() {  taskWin.actionCreate();  });


                    taskWin.setActionCreate(createTask);
             
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
                            })
                        }
                    });
                }

                document.querySelectorAll('.task-list > .project > .dropdown-header > h3').forEach(el => loadTasks(el));

                function loadNext(e) {
                    let el = e.target;
                    el.classList.add('disabled');
                    let projectEl = el.parentNode.parentNode;
            
                    let response = Request.Action('/api/get/task/' + projectEl.dataset.projectId + "?last=" + projectEl.dataset['last'], 'GET');
                    response.then(res => {
                        let tasks = res.data.tasks;

                        if (tasks.length > 0) 
                        {
                            projectEl.dataset['last'] = tasks[tasks.length - 1].id;
                        }

                        tasks.forEach(task => {
                            DATA.AddTask(projectEl.dataset.projectId, task, projectEl.querySelector('.container'));
                        });
                    }).finally(() => el.classList.remove('disabled'));
                }
            </script>
        </div>
<?php
}
}

?>