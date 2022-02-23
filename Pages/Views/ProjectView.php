<?php

declare (strict_types = 1);

namespace Kantodo\Views;

use function Kantodo\Core\Functions\base64DecodeUrl;
use function Kantodo\Core\Functions\t;
use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Models\ProjectModel;

/**
 * Projekt
 */
class ProjectView implements IView
{
    public function render(array $params = [])
    {
        
        $project     = $params['project'];
        $members     = $params['members'] ?? [];
        $projectUrlUUID = $params['projectUUID'];
        
        $icon = ((bool) $project['is_open'] === true) ? "lock_open" : "lock";
        $priv      = $params['priv'];

        Application::$APP->header->setTitle("Kantodo - " . $project['name']);

        ?>
        <script src="<?= Application::$SCRIPT_URL?>task.js"></script>
        <div class="container">
        <div class="row h-space-between">
        <h2 style="font-size: 2.8rem"><?=$project['name'];?><span class="icon big round"><?=$icon;?></span></h2>
            <div class="row">
                <button data-action='task' class="filled hover-shadow"><?=t('add_task', 'dashboard');?></button>
                <?php

        if ($priv['addOrRemovePeople'] === true) {
            ?>
                <a href="/project/<?= $projectUrlUUID;?>/settings">
                    <button class="flat icon outline space-medium-left">settings</button>
                </a>
        <?php }?>
                <script>
                window.addEventListener('load', function(){
                    let btn = document.querySelector('button[data-action=task]');
                    taskWin = Modal.createTaskWindow(btn, {id: "<?=$projectUrlUUID;?>", name: "<?=$project['name'];?>"});
                    
                    let editor = taskWin.getEditor();
                    let input = taskWin.getProjectInput();
                    input.parentElement.parentElement.classList.add('active');
                    taskWin.element.querySelector('[data-action=create]').addEventListener('click', function() {
                        if (taskWin.isError()) 
                        {
                            return;
                        }

                        let inputName = taskWin.element.querySelector('[data-input=task_name]');
                        let data = {};

                        data.task_name = inputName.value;
                        data.task_desc = editor.value();
                        data.task_proj = input.dataset.value;

                        let chipsArray = taskWin.getChips();
                        for (let i = 0; i < chipsArray.length; i++) {
                            data[`task_tags[${i}]`] = chipsArray[i];
                        }

                        let response = Request.Action('/api/create/task', 'POST', data);
                        response.then(res => {
                            let task = res.data.task;
                            Kantodo.success(`Created task (${task.id})`);

                            inputName.value = "";
                            editor.value("");
                            input.dataset.value = null;

                            taskWin.hide();

                            let snackbar = Modal.Snackbar.create('<?=t('task_was_created');?>', null ,'success');
                            snackbar.show();

                        }).catch(reason => {
                            Kantodo.error(reason);
                        }).finally(() => {
                            taskWin.hide();
                        });
                    });
                });
                </script>
            </div>
        </div>
            <h3 class="space-huge-top"><?= t('tasks_in_project', 'project') ?></h3>
            <div class="container space-medium-top" data-last="" style="max-height: 33%; overflow-y: scroll;flex-wrap:nowrap;">
                <div class="container">
                    <!-- Zde jsou ukoly projektu -->
                </div>
                <button onclick="loadNext(event)" class="hover-shadow flat no-border" style="margin: 10px auto"><?= t('load') ?></button>
                <script>
                showCompleted = true;
                function loadNext(e) {
                    let el = e.target;
                    el.classList.add('disabled');
                    let container = el.parentNode;

                    loadProjectTasks(
                        "<?=$projectUrlUUID;?>", 
                        container.dataset['last'],
                        function(tasks) { 
                            if (tasks.length > 0) 
                            {
                                container.dataset['last'] = tasks[tasks.length - 1].id;
                            }
                            tasks.forEach(task => {
                                DATA.AddTask("<?= $projectUrlUUID?>", task, container.querySelector('.container'));
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
            <h3 class="space-huge-top"><?=t('members');?></h3>
            <div class="row space-big-top">
                <?php

        if ($priv['addOrRemovePeople'] === true) {
            $text = t('create_invite_link', 'project');
            $add  = t('get_link', 'project');
            $dialogTitle = t('project_code', 'project');
            $close = t('close');
            $copy = t('copy');

            echo <<<HTML
            <div class="banner" style="margin-bottom: 20px">
                <span class="icon round medium">vpn_key</span>
                <p>{$text}</p>
                <div class="actions container">
                    <button data-action='project-code' class='hover-shadow filled' style="border-radius: 5px"><span class="icon round">add</span>{$add}</button>
                </div>
                <script>

                    document.querySelector('[data-action=project-code]').addEventListener('click', function() {
                        let response = Request.Action('/api/get/code/{$projectUrlUUID}', 'get');
                        response.then(obj => {
                            let dialog = Modal.Dialog.create('{$dialogTitle}', `<div style="border: 1px solid var(--font-400);border-radius: 4px;padding: 8px 10px;background: var(--font-100);">\${obj.data.code}</div>`, [
                                {
                                    'text': '{$close}', 
                                    'classList': 'flat no-border',
                                    'click': function(dialogOBJ) {
                                        dialogOBJ.destroy(true);
                                    }
                                },
                                {
                                    'text': '{$copy}',
                                    'classList': 'space-big-left text',
                                    'click': function(dialogOBJ) {

                                        let text = obj.data.code;

                                        if (!navigator.clipboard) {
                                            let textArea = document.createElement("textarea");
                                            textArea.value = text;
                                            
                                            // Avoid scrolling to bottom
                                            textArea.style.top = "0";
                                            textArea.style.left = "0";
                                            textArea.style.position = "fixed";

                                            document.body.appendChild(textArea);
                                            textArea.focus();
                                            textArea.select();

                                            try {
                                                document.execCommand('copy');
                                            } catch (err) {
                                                Kantodo.error(err);
                                            }

                                            document.body.removeChild(textArea);
                                            dialogOBJ.destroy(true);
                                            return;
                                        }
                                        navigator.clipboard.writeText(text).catch(function(err) {
                                            Kantodo.error(err);
                                        });

                                        dialogOBJ.destroy(true);
                                    }
                                }
                            ]);
                            
                            dialog.setParent(document.body.querySelector('main'));
                            

                            dialog.show();

                        }).catch(err => {
                            Kantodo.error(err);
                        });
                    });


                </script>
            </div>
            HTML;
        }
        foreach ($members as $member): ?>
                    <div class="avatar fullname space-regular-right">
                        <?=$member['firstname'] . ' ' . $member['lastname'];?>
                    </div>
        <?php endforeach;?>
            </div>
        </div>

<?php
}
}

?>