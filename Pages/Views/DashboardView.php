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
                <button data-action='task' class="filled"><?= t('add_task', 'dashboard') ?></button>
                <button class="flat icon outline space-medium-left">settings</button>
                <script>
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

                            let response = Request.Action('/api/create/task', 'POST', data);
                            response.then(res => {
                                let task = res.data.task;
                                Kantodo.success(`Created task (${task.id})`);
                        
                                inputName.value = "";
                                win.getEditor().value("");
                                win.getProjectInput().dataset.value = null;

                                win.hide();

                                let snackbar = Modal.Snackbar.create('<?= t('task_was_created') ?>', null ,'success');

                                snackbar.setParent(document.body.querySelector('main'));
                                snackbar.show({center: true, top: 5}, 4000, false);

                            }).catch(reason => {
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
                        <button class="flat icon round">filter_alt</button>
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

                            // TODO: nacteni ukolu
                            let response = Request.Action('/api/get/task/' + projectEl.dataset.projectId, 'GET');
                            response.then(res => {
                                let tasks = res.data.tasks;
                                let tmp = '';
                                tasks.forEach(task => {
                                    
                                    let tags = task.tags;

                                    let tagsHTML = tags.map(tag => {
                                        return `<div class="tag">${tag}</div>`;
                                    }).join('');

                                    console.log(tagsHTML);

                                    tmp += `<div class="task">
                                                <header>
                                                    <div>
                                                        <label class="checkbox">
                                                            <input type="checkbox">
                                                            <div class="background"></div>
                                                        </label>
                                                        <h4>${task.name}</h4>
                                                    </div>
                                                    <div>
                                                        <button class="flat no-border icon round">more_vert</button>
                                                    </div>
                                                </header>
                                                <footer>
                                                    <div class="avatars">
                                                        <div class="avatar"></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="tags">
                                                            ${tagsHTML}
                                                        </div>
                                                        <div class="row middle"><span class="space-small-right">3 Comments</span><span class="icon round">chat_bubble</span></div>
                                                    </div>
                                                </footer>
                                            </div>`;
                                });

                                projectEl.querySelector('.container').innerHTML = tmp;
                            })
                        }
                    })
                });
            </script>
            <!--<div class="milestone-list">
                <!--<div class="milestone">
                    <div class="date">
                        <span class="month">Sep</span>
                        <span class="day">18</span>
                    </div>
                    <div class="container">
                        <p>Write 15 blog articles on Medium</p>
                        <span class="description">Office/Marketing</span>
                        <div class="progress-bar">
                            <div class="bar">
                                <div class="completed" style="width: 72%;"></div>
                            </div>
                            <span>72% Completed</span>
                        </div>
                    </div>
                </div>
                <div class="milestone">
                    <div class="date">
                        <span class="month">Nov</span>
                        <span class="day">02</span>
                    </div>
                    <div class="container">
                        <p>Publish 20 dribbbles</p>
                        <span class="description">Office/Marketing</span>
                        <div class="progress-bar">
                            <div class="bar">
                                <div class="completed" style="width: 15%;"></div>
                            </div>
                            <span>15% Completed</span>
                        </div>
                    </div>
                </div>
            </div>-->
        </div>
<?php
}
}

?>