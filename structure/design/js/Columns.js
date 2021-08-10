/*class Column {
    constructor(element) {
        this.__element = element;
        this.__tasksElement = element.getElementsByClassName('tasks')[0];
        
        let btns = element.getElementsByTagName('button');

        btns[0].onclick = this.addTask;
        btns[1].onclick = this.options;
    }

    static getAllColumns() {
        let columnElements = document.getElementsByClassName('column');

        let columns = [];

        for (let index = 0; index < columnElements.length; index++) {
            const element = columnElements[index];

            columns.push(new Column(element));
        }

        return columns;
    }
    static create(name, parent, offset = 0) {
        let tmp = document.createElement('div');

        tmp.innerHTML = `
        <div class='column'>
            <div class='row'>
                <div class='title'>${name}</div>
                <div class='actions'>
                    <button class='icon-small round flat'>
                        <span class='material-icons-round'>add</span>
                    </button>
                    <button class='icon-small round flat'>
                        <span class='material-icons-round'>more_horiz</span>
                    </button>
                </div>
            </div>
            <div class='tasks' data-drop-area='task'></div>
        </div>`;
        let element = tmp.children[0];
        parent.insertBefore(element, parent.children[parent.children.length - offset]);
        return new Column(element);
    }
    addTask() {
        console.log('ADDING TASK');
    }
    
    options() {
        console.log('OPTIONS');
    }

    /*
    task => {
        'name': '',
        'id': 0,
        'tags': [
            {
                'name': 'info',
                'color': 'red'
            },
            {
                'name': 'warning',
                'color': 'blue'
            }
        ],
        'identifier': 0,
        'comments': 0,
        'attachments': 0,
        'priority': 0-3
        'users': [
            {
                'name': 'Jan',
                'surname': 'Hon',
                'background': 'red';
            },
            {
                'name': 'Petr',
                'surname': 'Hon',
                'background': 'green';
            },
        ]
    }



    insert(task, offsetBottom = 0) {
        let priority = (task.priority == 0) ? 'var(--info)' : (task.priority == 1) ? 'var(--warning)' : 'var(--error)';

        let taskElement = document.createElement('div');
        taskElement.className = 'task';
        taskElement.dataset['drop'] = 'task';
        taskElement.dataset['taskId'] = task.identifier;
        taskElement.draggable = false;
        taskElement.onmousedown = function() {
            taskElement.draggable = true;
        }

        taskElement.onmouseup = function() {
            taskElement.draggable = false;
        }


        let html = `
            <div class='head'>
                <div class='name'>${task.name}</div>
                <button class='icon-small flat round'><span class='material-icons-round'>more_horiz</span></button>
            </div>
            <div class='content'>
                <div class='identifier'>${task.identifier}</div>
                <div class='priority' style='--tag-priority: ${priority}'></div>
                <div class='tags'>`;

        task.tags.forEach(tag => {
            html += `<div class='tag' style='--tag-color: ${tag.color}'>${tag.name}</div>`;
        });

        html +=`
                </div>
            </div>
            <div class='footer'>
                <button class='icon-small flat round'>
                    <span class='material-icons-round'>attach_file</span>${task.attachments}
                </button>
                <button class='icon-small flat round'>
                    <span class='material-icons-outlined'>chat</span>${task.comments}
                </button>
                <div class='avatars'>`;

        task.users.forEach(user => {
            let avatarName = user.name[0] + user.surname[0];

            html += `<div class='avatar' style='--avatar-color: ${user.background}'><p>${avatarName}</p></div>`;
        });
            
        html +=`
                </div>
            </div>`;


        taskElement.innerHTML = html;

        if (offsetBottom == 0) {
            this.__tasksElement.appendChild(taskElement);
        } else {
            this.__tasksElement.insertBefore(taskElement, this.__tasksElement.children[this.__tasksElement.children.length - offsetBottom]);
        }    
    }
}
*/