<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

/**
 * Projekt
 */
class ProjectView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->registerStyle('/styles/project.css');
        Application::$APP->header->registerScript('/scripts/column.js');

        $membersInitials = $params['membersInitials'] ?? [];

        $membersCount = count($membersInitials);
        $rawProjID    = $params['projID'];

        ?>
        <h2><?=$params['teamName'];?></h2>
        <div class="avatars">
            <?php

        for ($i = 0; $i < 5 && $i < $membersCount; $i++) {
            $initials = $membersInitials[$i];
            ?>
                <div class="avatar">
                    <p><?=$initials;?></p>
                </div>
            <?php
}

        if ($membersCount > 5) {
            ?>
                <div class="avatar">
                    <p>+<?=$membersCount - 5;?></p>
                </div>
            <?php
}

        ?>
            <button class="text icon-text"><span class="material-icons-round">add</span>invite</button>
        </div>
        <div class="actions row">
            <button class="primary icon-text"><span class="material-icons-round">add</span>Create</button>
            <button class="icon-text flat"><span class="material-icons-round">filter_alt</span>Filter</button>
        </div>
        <div id="columns">
        <?php

        foreach ($params['columns'] ?? [] as $column):
        ?>
            <div class="column" data-column="<?=base64_encode($column['id']);?>">
                <div class="row">
                    <div class="title"><?=$column['name'];?></div>
                    <div class="actions">
                        <button class="icon-small round flat">
                            <span class="material-icons-round">add</span>
                        </button>
                        <button class="icons-small flat">
                            <span class="material-icons-round">more_horiz</span>
                        </button>
                    </div>
                </div>
                <div class="tasks" data-drop-area="task">
                <?php

        foreach ($column['tasks'] as $task):
        ?>
                    <div class="task">
                        <div class="head">
                            <div class="name"><?=$task['name'];?></div>
                            <button class="icon-small flat round"><span class="material-icons-round">more_horiz</span></button>
                        </div>
                        <div class="content">
                            <div class="identifier">1</div>
                            <div class="priority"></div>
                            <div class="tags">
                                <div class="tag">TAG NAME</div>
                            </div>
                        </div>
                        <div class="footer">
                            <button class="icon-small flat round">
                                <span class="material-icons-round">attach_file</span>0
                            </button>
                            <button class='icon-small flat round'>
                                <span class='material-icons-outlined'>chat</span>1
                            </button>
                            <div class="avatars">
                                <div class="avatar">LK</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
                </div>
            </div>
        <?php endforeach;?>
            <!-- <div class="column">
                <div class="row">
                    <div class="title">To Do</div>
                    <div class="actions">
                        <button class="icon-small round flat">
                            <span class="material-icons-round">add</span>
                        </button>
                        <button class="icon-small round flat">
                            <span class="material-icons-round">more_horiz</span>
                        </button>
                    </div>
                </div>
                <div class="tasks" data-drop-area="task">
                    <div class="task">
                        <div class="head">
                            <div class="name">NAME</div>
                            <button class="icon-small flat round"><span class="material-icons-round">more_horiz</span></button>
                        </div>
                        <div class="content">
                            <div class="identifier">1</div>
                            <div class="priority"></div>
                            <div class="tags">
                                <div class="tag">TAG NAME</div>
                            </div>
                        </div>
                        <div class="footer">
                            <button class="icon-small flat round">
                                <span class="material-icons-round">attach_file</span>0
                            </button>
                            <button class='icon-small flat round'>
                                <span class='material-icons-outlined'>chat</span>1
                            </button>
                            <div class="avatars">
                                <div class="avatar">LK</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <button id="addColumn">
                <span>Add column</span>
            </button>
        </div>
        <script>
            (function() {
                'use-strict';

                ///////////////////
                // CREATE COLUMN //
                ///////////////////
                let addColumnBtn = document.getElementById('addColumn');
                let contentColumn = `
            <div class="container">
                <div class="container space-top">
                    <label>
                        <div class="text-field outline">
                            <input type="text" name="columnName" required>
                            <div class="label">Name</div>
                        </div>
                        <div class="error-msg"></div>
                    </label>
                </div>
                <div class="container space-top">
                    <label>
                        <div class="text-field outline">
                            <input type="number" min="0" name="maxTasksCount" required>
                            <div class="label">Max tasks in column</div>
                        </div>
                        <div class="error-msg"></div>
                    </label>
                </div>
                <div class='row main-center space-top'>
                    <button class='long primary'>Create</button>
                <div>
            </div>
            `;

                let columnFormWindow = createFormWindow('Create column', contentColumn, '<?=Application::$URL_PATH;?>/project/<?=$rawProjID;?>/create/column', function(self) {
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
                            /*let res = JSON.parse(result);
                            if (res.data == true)
                            {
                                // TODO ADD TEAM TO LIST

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

                addColumnBtn.onclick = function() {
                    if (!columnFormWindow.isOpened)
                        columnFormWindow.show()
                }


                /////////////////
                // CREATE TASK //
                /////////////////

                let columns = document.querySelectorAll('[data-column]');
                let contentTask = `
            <div class="container">
                <input type="hidden" name='column'>
                <div class="container space-top">
                    <label>
                        <div class="text-field outline">
                            <input type="text" name="taskName" required>
                            <div class="label">Name</div>
                        </div>
                        <div class="error-msg"></div>
                    </label>
                </div>
                <div class="container space-top">
                    <label>
                        <div class="text-field outline">
                            <input type="text" min="0" name="taskDescription" required>
                            <div class="label">Description</div>
                        </div>
                        <div class="error-msg"></div>
                    </label>
                </div>
                <div class="container space-top">
                    <label>
                        <div class="text-field outline">
                            <input type="number" min="0" max="255" name="taskPriority" required>
                            <div class="label">Priority</div>
                        </div>
                        <div class="error-msg"></div>
                    </label>
                </div>
                <div class="container space-top">
                    <label>
                        <div class="text-field outline">
                            <input type="date" name="taskEndDate" required>
                            <div class="label">End date</div>
                        </div>
                        <div class="error-msg"></div>
                    </label>
                </div>
                <div class='row main-center space-top'>
                    <button class='long primary'>Create</button>
                <div>
            </div>
            `;


                let taskFormWindow = createFormWindow('Create task', contentTask, '<?=Application::$URL_PATH;?>/project/<?=$rawProjID;?>/create/task', function(self) {

                    let btn = self.$('button')[0];
                    btn.onclick = function() {
                        let inputs = self.$('input');

                        // hidden input
                        inputs[0].value = self.column;


                        let params = {};
                        let error = false;
                        for (let i = 0; i < inputs.length; i++) {
                            const element = inputs[i];

                            let parent = element.parentNode;

                            if (element.value == '') {
                                //parent.classList.add('error');
                                //parent.parentNode.children[1].innerText = 'Empty';
                                error = true;
                            } else {
                                //parent.classList.remove('error');
                                //parent.parentNode.children[1].innerText = '';
                            }
                            params[element.name] = element.value;
                        }

                        if (error)
                            return;

                        const request = self.request(params);
                        request.then(result => {
                            console.log(result);
                            /*let res = JSON.parse(result);
                            if (res.data == true)
                            {
                                // TODO ADD TEAM TO LIST

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

                columns.forEach(col => {
                    let colObj = Column.init(col);
                    colObj.addTaskAction = function(event, column) {
                        if (!taskFormWindow.isOpened) {
                            taskFormWindow.column = column;
                            taskFormWindow.show();
                        }
                    }

                    colObj.optionsAction = function(event) {
                        console.log("OPTIONS");
                    }

                });

            })();


            let ws = new WebSocket('ws://localhost:8443/Kantodo/websockets');

            function send(text){
                ws.send(text);
            }
        </script>
<?php
}
}

?>