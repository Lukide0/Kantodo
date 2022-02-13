let showCompleted = false;

function showCompletedTasks(e) 
{
    if (showCompleted) 
    {
        showCompleted = false;
        for (const projUUID in DATA.Projects) {
            DATA.Projects[projUUID].tasks.forEach(task => {
            if (task.completed == "1") {
                document.querySelector(`[data-task-id="${task.id}"]`).style.display = "none";
            }
            });
        }

        e.target.innerText = translations['%show_completed_tasks%'];

    } else {
        showCompleted = true;
        for (const projUUID in DATA.Projects) {
            DATA.Projects[projUUID].tasks.forEach(task => {
            if (task.completed == "1") 
            {
                document.querySelector(`[data-task-id="${task.id}"]`).style.display = null;
            }
            });
        }
        e.target.innerText = translations['%hide_completed_tasks%'];
    }
}


DATA.AfterTaskRemove = function(uuid, taskID)
{
    // TODO: check if user edit
    let el = document.querySelector(`[data-task-id='${taskID}']`);
    if (el)
        el.remove();
}

DATA.AfterTaskUpdate = function(uuid, index, data)
{
    // TODO: check if user edit
}

DATA.AfterTaskAdd = function(uuid, task, container) {
    let tags = task.tags;
    let tagsHTML = tags.map(tag => {
        return `<div class="tag">${tag}</div>`;
    }).join('');

    let taskEl = document.createElement('div');
    if (task.completed == '1' && showCompleted == false) 
    {
        taskEl.style.display = "none";
    }
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
                'text':  translations['%close%'], 
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
            setEndDate(taskInfo.end_date);
        
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
                taskData.end_date = new Date(date).toJSON();

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
            response.then(_ => {
                if (taskData.name != taskInfo.name) 
                {
                    taskEl.querySelector(`header h4`).innerText = taskData.name;
                }

                for(var p in taskData)
                {
                    taskInfo[p] = taskData[p];
                }
                DATA.UpdateTask(uuid, taskInfo);
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

        let dialog = Modal.Dialog.create(translations['%confirm%'], translations['%do_you_want_delete_this_task%'], [{
                'text':  translations['%close%'], 
                'classList': 'flat no-border',
                'click': function(dialogOBJ) {
                    dialogOBJ.destroy(true);
                    return false;
                }
            }, {
                'text': translations['%yes%'],
                'classList': 'space-big-left text error',
                'click': deleteTask
            }
        ]);
        dialog.setParent(document.body);
        menu.element.blur();
        dialog.show();

        function deleteTask(dialogOBJ, e) {
            let data = {
                'task_id': `${taskInfo.id}`,
                'task_proj': uuid
            }
            e.target.classList.add('disabled');

            let response = Request.Action('/api/remove/task', 'POST', data);
            response.then(_ => {
                
                DATA.RemoveTask(uuid, taskInfo.id);
                taskEl.remove();

                let snackbar = Modal.Snackbar.create(translations['%task_was_removed%'], null ,'success');
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
        itemChangeStatus = Dropdown.Item.create(translations['%mark_as_completed%'], switchStatusComplete, {'text': 'done'});
    } else {
        itemChangeStatus = Dropdown.Item.create(translations['%mark_as_incomplete%'], switchStatusUncomplete, {'text': 'close'});
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

    function switchStatusComplete() 
    {
        let data = {
            task_proj: uuid,
            task_id: taskInfo.id,
            task_comp: '1',
        };

        let response = Request.Action('/api/update/task', 'POST', data);
        response.then(res => {
            taskInfo.completed = 1;

            if (showCompleted == false) 
            {
                taskEl.style.display = "none";
            }

            DATA.UpdateTask(uuid, taskInfo);

        }).catch(reason => {
            let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');
            snackbar.show();
            Kantodo.error(reason);
        }).finally(() => {
            taskWin.hide();
            menu.element.blur()
        });
    }

    function switchStatusUncomplete() 
    {
        let data = {
            task_proj: uuid,
            task_id: taskInfo.id,
            task_comp: '0',
        };

        let response = Request.Action('/api/update/task', 'POST', data);
        response.then(res => {
            taskInfo.completed = 0;
        }).catch(reason => {
            let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');
            snackbar.show();
            Kantodo.error(reason);
        }).finally(() => {
            taskWin.hide();
            menu.element.blur()
        });
    }
}

function loadProjectTasks(uuid, lastID, callbackData, callbackAfter) 
{
    let response = Request.Action('/api/get/task/' + uuid + "?last=" + lastID, 'GET');
    response.then(res => {
        let tasks = res.data.tasks;
        callbackData(tasks);   
    }).finally(callbackAfter);
}
