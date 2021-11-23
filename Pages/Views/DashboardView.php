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
                <div class="button-dropdown">
                    <button data-action='task' class="filled"><?= t('add_task', 'dashboard') ?></button>
                    <button class="dropdown icon round">expand_more</button>
                </div>
                <button class="flat icon outline space-medium-left">settings</button>
                <script>
                    const TaskList = [];
                    const modalTaskHTML = `
                    <div class="content">
                        <label class="text-field">
                            <div class="field">
                                <span><?= t('task_name', 'dashboard') ?></span>
                                <input type="text" data-input='task_name'>
                            </div>
                            <div class="text"></div>
                        </label>
                        <div class="editor">
                            <textarea></textarea>
                        </div>
                        <div class="actions">
                            <button class="flat"><?= t('attachment', 'dashboard') ?></button>
                        </div>
                    </div>
                    <div class="settings">
                    <label class="text-field selector outline">
                        <div class="field">
                            <span><?= t('select_project', 'dashboard') ?></span>
                            <input type="text" data-input='project' data-value=''>
                        </div>
                        <ul class="options dropdown-menu" data-select='project' tabindex='-1'></ul>
                    </label>
                        <div class="attributes">
                            <div class="title">Attributes</div>
                            <div class="attribute-list">
                                <div class="attribute">
                                    <div class="name">Status</div>
                                    <label class="text-field selector">
                                        <div class="field">
                                            <input type="text" data-input='project' data-value=''>
                                        </div>
                                        <ul class="options dropdown-menu" data-select='project' tabindex='-1'>
                                            <li>Open</li>
                                            <li>Closed</li>
                                        </ul>
                                    </label>
                                </div>
                                <div class="attribute">
                                    <div class="name">Priority</div>
                                    <label class="text-field selector">
                                        <div class="field">
                                            <input type="text" data-input='project' data-value=''>
                                        </div>
                                        <ul class="options dropdown-menu" data-select='project' tabindex='-1'>
                                            <li>Low</li>
                                            <li>Medium</li>
                                            <li>High</li>
                                        </ul>
                                    </label>
                                </div>
                                <div class="attribute">
                                    <div class="name">Assignee</div>
                                    <div class="value disabled">Lukas Koliandr</div>
                                </div>
                                <div class="attribute">
                                    <div class="name">Due date</div>
                                    <div class="value"><span class="icon extra-small outline">calendar_today</span>14-01-2022</div>
                                    <button class="icon outline flat no-border">close</button>
                                </div>
                                <div class="attribute">
                                    <div class="name">Tags</div>
                                    <div class="value">
                                        <div class="chips-container">
                                            <div class="chips">
                                                <div class="chip"><span>Name</span><button class="icon outline flat no-border">close</button></div>
                                                <div class="chip"><span>Name</span><button class="icon outline flat no-border">close</button></div>
                                            </div>
                                            <label class="row middle">
                                                <span class="icon small outline">search</span>
                                                <input type='text' id="tagInput">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="actions">
                            <button data-action="close" class="flat"><?= t('cancel') ?></button>
                            <button data-action="create" class="hover-shadow"><?= t('create') ?></button>
                        </div>
                    </div>
                    `
                    window.addEventListener('load', function(){
                        let btn = document.querySelector('button[data-action=task]');
                        let win = Modal.EditorModalWindow.create(modalTaskHTML);
                        let editor = new SimpleMDE({
                            element: win.element.querySelector('textarea'),
                            renderingConfig: {
                                codeSyntaxHighlighting: true,
                            },
                            tabSize: 4,
                            spellChecker: false,
                            toolbar: ['bold', 'italic', 'strikethrough', '|', 'heading-1', 'heading-2', 'heading-3', '|', 'quote', 'link', 'table', '|', 'unordered-list', 'ordered-list', '|' , 'preview', 'guide']
                        });

                        // FIX: bug -> při smazání se neposune span dolů
                        let menu = win.element.querySelector('[data-select=project]');
                        let input = win.element.querySelector('[data-input=project]');
                        let textField = input.parentElement.parentElement;
                        function createOptions() {
                            if (input.value.length != 0)
                                textField.classList.remove('active');

                            menu.innerHTML = "";
                            let options;

                            // filter
                            options = Projects.filter(proj => proj.name.toLowerCase().includes(input.value.toLowerCase()));

                            if (options.length == 0) 
                            {
                                textField.classList.add('error');
                                textField.classList.add('active')
                                return;
                            } else {
                                textField.classList.remove('error');
                            }

                            options.forEach(project => {
                                let item = document.createElement('li');
                                item.textContent = project.name;
                                item.dataset.projectId = project.id;
                                item.onclick = function(e) {
                                    input.dataset.value = project.id;
                                    input.value = item.textContent;
                                    textField.classList.add('active');
                                    e.preventDefault();
                                    input.blur();
                                    console.trace("test");
                                }
                                menu.appendChild(item);
                            });
                        }

                        createOptions();
                        input.addEventListener('input', createOptions);
                        input.addEventListener('click', createOptions);

                        win.element.querySelector('[data-action=create]').addEventListener('click', function() {
                            let inputName = win.element.querySelector('[data-input=task_name]');
                            let data = {};

                            data.task_name = inputName.value;
                            data.task_desc = editor.value();
                            data.task_proj = input.dataset.value;

                            let response = Request.Action('/api/create/task', 'POST', data);
                            response.then(res => {
                                let task = res.data.task;
                                Kantodo.success(`Created task (${task.id})`);
                        
                                inputName.value = "";
                                editor.value("");
                                input.dataset.value = null;

                                win.hide();

                                let snackbar = Modal.Snackbar.create('<?= t('task_was_created') ?>', null ,'success');

                                snackbar.setParent(document.body.querySelector('main'));
                                snackbar.show({center: true, top: 5}, 4000, false);

                            }).catch(reason => {
                                Kantodo.error(`{ status: ${reason.status}, statusText: ${reason.statusText} }`);
                            });
                        });

                        win.setParent(document.body.querySelector('main'));

                        btn.addEventListener('click', function(e) {
                            win.show();
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
                                                        <div class="important">Important</div>
                                                        <button class="flat no-border icon round">more_vert</button>
                                                    </div>
                                                </header>
                                                <footer>
                                                    <div class="avatars">
                                                        <div class="avatar"></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="tags">
                                                            <div class="tag">New Client</div>
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