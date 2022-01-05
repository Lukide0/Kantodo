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
        // TODO: pridavani ukolu
        // TODO: pridavani projektu s ukoly po vytvoreni
        ?>
        <div class="row h-space-between">
            <h2><?= t('my_work', 'dashboard') ?></h2>
            <div class="row">
                <button data-action='task' class="filled big hover-shadow"><?= t('add_task', 'dashboard') ?></button>
                <script>
                    DATA.AddTask = function(uuid, task, container) 
                        {

                            if (typeof this.Projects[uuid] !== "object") 
                            {
                                return;
                            }
                            this.Projects[uuid].tasks.push(task);
                            
                            let tags = task.tags;
                            let tagsHTML = tags.map(tag => {
                                return `<div class="tag">${tag}</div>`;
                            }).join('');
                            let tmp = `<div class="task">
                                        <header>
                                            <div>
                                                <label class="checkbox">
                                                    <input type="checkbox">
                                                    <div class="background"></div>
                                                </label>
                                                <h4>${task.name}</h4>
                                            </div>
                                            <div>
                                                <button class="flat no-border icon round" onclick="showTaskContextMenu(event);">more_vert</button>
                                            </div>
                                        </header>
                                        <footer>
                                            <div class="row">
                                                <div class="tags">
                                                    ${tagsHTML}
                                                </div>
                                            </div>
                                        </footer>
                                    </div>`;
                            container.innerHTML += tmp;
                        };

                    let menu;
                    function showTaskContextMenu(e) {
                        if (menu)
                            menu.element.remove();
                        let {x, y} = e;

                        menu = Dropdown.Menu.create();

                        let itemEdit = Dropdown.Item.create('edit', null, {'text': 'edit'});
                        let itemRemove = Dropdown.Item.create('remove', null, {'text': 'delete'});
                        let itemMarkAsCompleted = Dropdown.Item.create('Mark as completed', null, {'text': 'done'});
                        menu.items.push(itemEdit, itemRemove, itemMarkAsCompleted);

                        menu.render();
                        document.body.append(menu.element);
                        menu.element.setAttribute("tabindex", -1);
                        menu.element.focus();
                        menu.element.addEventListener('blur', function() {
                            menu.element.remove();
                            menu = null;
                        });
                        let width = menu.element.offsetWidth;

                        menu.move(x - width, y);
                    }

                    window.addEventListener('load', function(){
                        let btn = document.querySelector('button[data-action=task]');
                        let win = Modal.createTaskWindow(btn);

                        win.element.querySelector('[data-action=create]').addEventListener('click', function() {
                            let inputName = win.element.querySelector('[data-input=task_name]');
                            let data = {};

                            data.task_name = inputName.value;
                            data.task_desc = win.getEditor().value();
                            data.task_proj = win.getProjectInput().dataset.value;
                            
                            let chipsArray = win.getChips();
                            for (let i = 0; i < chipsArray.length; i++) {
                                data[`task_tags[${i}]`] = chipsArray[i];
                            }

                            let projectEl = document.querySelector(`main [data-project-id=${data.task_proj}] > .container`);
                            let taskData = {
                                completed: "0",
                                description: data.task_desc,
                                tags: chipsArray,
                                // TODO:
                                end_date: null,
                                name: data.task_name,
                                priority: "1",
                            };
                            let response = Request.Action('/api/create/task', 'POST', data);
                            response.then(res => {
                                let task = res.data.task;
                                Kantodo.success(`Created task (${task.id})`);
                                taskData.id = task.id;
                                DATA.AddTask(data.task_proj, taskData, projectEl);

                                inputName.value = "";
                                win.getEditor().value("");
                                win.getProjectInput().dataset.value = null;

                                win.hide();

                                let snackbar = Modal.Snackbar.create('<?= t('task_was_created') ?>', null ,'success');

                                snackbar.setParent(document.body.querySelector('main'));
                                snackbar.show({center: true, top: 5}, 4000, false);

                            }).catch(reason => {

                                let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');

                                snackbar.setParent(document.body.querySelector('main'));
                                snackbar.show({center: true, top: 5}, 4000, false);
                                Kantodo.error(reason);
                            });
                        });
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
                    <div class="container"></div>
                </div>
                <?php endforeach; ?>
            </div>
            <script>
                document.querySelectorAll('.task-list > .project > .dropdown-header > h3').forEach(el => {
                    el.addEventListener('click', function() {
                        if (el.parentElement.parentElement.classList.contains('expanded'))
                            el.parentElement.parentElement.classList.remove('expanded');
                        else {
                            let projectEl = el.parentElement.parentElement;
                            projectEl.classList.add('expanded');
                            
                            if (projectEl.dataset.loaded)
                                return;

                            projectEl.dataset.loaded = true;

                            // TODO: zobrazeni ukolu
                            let response = Request.Action('/api/get/task/' + projectEl.dataset.projectId, 'GET');
                            response.then(res => {
                                let tasks = res.data.tasks;
                                tasks.forEach(task => {
                                    DATA.AddTask(projectEl.dataset.projectId, task, projectEl.querySelector('.container'));
                                });
                            })
                        }
                    })
                });
            </script>
        </div>
<?php
}
}

?>