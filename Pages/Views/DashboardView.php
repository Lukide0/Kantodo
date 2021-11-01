<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Widgets\Task;

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
    public function Render(array $params = [])
    {
        // TODO: pridavani ukolu
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
                            <div class="menu">
                                <ul class="group">
                                    <li><button class="flat no-border no-padding" data-tooltip="undo" data-action="undo" data-select="none"><span class="icon round">undo</span></button></li>
                                    <li><button class="flat no-border no-padding" data-tooltip="redo" data-action="redo"><span class="icon round">redo</span></button></li>
                                </ul>
                                <ul class="group">
                                    <li><button class="flat no-border no-padding" data-tooltip="bold" data-action="bold"><span class="icon round">format_bold</span></button></li>
                                    <li><button class="flat no-border no-padding" data-tooltip="italic" data-action="italic"><span class="icon round">format_italic</span></button></li>
                                </ul>
                                <ul class="group">
                                    <li><button class="flat no-border no-padding" data-tooltip="heading" data-action="heading" data-select="none"><span class="icon round">format_size</span></button></li>
                                    <li><button class="flat no-border no-padding" data-tooltip="strikethrough" data-action="strikethrough"><span class="icon round">strikethrough_s</span></button></li>
                                    <li><button class="flat no-border no-padding" data-tooltip="quote" data-action="quote" data-select="none"><span class="icon round">format_quote</span></button></li>
                                </ul>
                                <ul class="group">
                                    <li><button class="flat no-border no-padding" data-tooltip="list" data-action="list" data-select="none"><span class="icon round">format_list_bulleted</span></button></li>
                                    <li><button class="flat no-border no-padding" data-tooltip="checklist" data-action="list" data-select="none"><span class="icon round">checklist</span></button></li>
                                </ul>
                                <ul class="group">
                                    <li><button class="flat no-border no-padding" data-tooltip="code" data-action="code"><span class="icon round">code</span></button></li>
                                </ul>
                                <button class="space-huge-left mode flat no-border">normal mode</button>
                            </div>
                            <div class="editable" contenteditable="true"></div>
                        </div>
                        <div class="sub-tasks">
                            <div class="title">
                                <p>sub-tasks</p>
                                <div class="progress-bar">
                                    <div class="bar">
                                        <div class="completed" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tasks">
                                <div class="sub-task">
                                    <span class="icon medium round">drag_indicator</span>
                                    <label class="checkbox success small">
                                        <input type="checkbox">
                                        <div class="background"></div>
                                    </label>
                                    <p>Create real time socket for agenda</p>
                                </div>
                                <div class="actions">
                                    <button class="flat action"><?= t('add_task', 'dashboard') ?></button><span><?= t('or') ?></span><button class="flat action"><?= t('create_task', 'dashboard') ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="actions">
                            <button class="flat"><?= t('attachment', 'dashboard') ?></button>
                        </div>
                    </div>
                    <div class="settings">
                        <button class="flat">
                            <span class="icon outline colored">dashboard</span><p class="space-regular-right"><?= t('select_project', 'dashboard') ?></p>
                        </button>
                        <div class="attributes">
                            <div class="title">Attributes</div>
                            <div class="attribute-list">
                                <div class="attribute">
                                    <div class="name">Status</div>
                                    <div class="value warning"><span class="dot"></span>In progress</div>
                                </div>
                                <div class="attribute">
                                    <div class="name">Priority</div>
                                    <div class="value error"><span class="dot"></span> High</div>
                                </div>
                                <div class="attribute">
                                    <div class="name">Assignee</div>
                                    <div class="value no-color">Lukas Koliandr</div>
                                </div>
                                <div class="attribute">
                                    <div class="name">Due date</div>
                                    <div class="value no-color"><span class="icon extra-small outline">calendar_today</span>14-01-2022</div>
                                    <button class="icon outline flat no-border">close</button>
                                </div>
                                <button class="add-attribute flat no-border">
                                    <span class="icon outline">add</span>
                                    <p>Add attribute</p>
                                </button>
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

                        win.element.querySelector('[data-action=create]').addEventListener('click', function() {
                            let inputs = win.element.querySelectorAll('[data-input]');
                            let data = {};

                            inputs.forEach(input => {
                                data[input.dataset.input] = input.value;
                            });

                            let response = Request.Action('/API/create/task', 'POST', data);
                            response.then(res => {
                                let task = res.data.task;
                                Kantodo.success(`Created task (${project.uuid})`);
                                
                                win.clear();

                                win.hide();

                                let snackbar = Modal.Snackbar.create('<?= t('task_was_created') ?>');

                                snackbar.setParent(document.body.querySelector('main'));
                                snackbar.show({center: true, top: 5}, 4000, true);

                            }).catch(reason => {
                                Kantodo.error(reason);
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
                <!--<div class="dropdown-header">
                    <h3>KantodoApp</h3>
                    <div class="line"></div>
                    <button class="flat icon round">filter_alt</button>
                </div>
                <div class="container">
                </div>-->
            </div>
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