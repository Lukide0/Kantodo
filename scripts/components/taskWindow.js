const TaskList = [];
const modalTaskHTML = `
<div class="content">
    <label class="text-field">
        <div class="field">
            <span>%task_name%</span>
            <input type="text" data-input='task_name'>
        </div>
        <div class="text"></div>
    </label>
    <div class="editor">
        <textarea></textarea>
    </div>
</div>
<div class="settings">
<label class="text-field selector outline">
    <div class="field">
        <span>%select_project%</span>
        <input type="text" data-input='project' data-value=''>
    </div>
    <ul class="options dropdown-menu" data-select='project' tabindex='-1'></ul>
</label>
    <div class="attributes">
        <div class="title">Attributes</div>
        <div class="attribute-list">
            <div class="attribute">
                <div class="name">%status%</div>
                <label class="text-field selector">
                    <div class="field">
                        <input type="text" data-input='status' data-value='0' value="%open%" readonly>
                    </div>
                    <ul class="options dropdown-menu" data-select='status' tabindex='-1'>
                        <li data-value='0'>%open%</li>
                        <li data-value='1'>%closed%</li>
                    </ul>
                </label>
            </div>
            <div class="attribute">
                <div class="name">%priority%</div>
                <label class="text-field selector">
                    <div class="field">
                        <input type="text" data-input='priority' data-value='0' value="%priority_low%" readonly>
                    </div>
                    <ul class="options dropdown-menu" data-select='priority' tabindex='-1'>
                        <li data-value='0'>%priority_low%</li>
                        <li data-value='1'>%priority_medium%</li>
                        <li data-value='2'>%priority_high%</li>
                    </ul>
                </label>
            </div>
            <div class="attribute">
                <div class="name">%date_of_completion%</div>
                <div class="value">
                    <input type='datetime-local' id="endDate">
                </div>
            </div>
            <div class="attribute">
                <div class="name">%tags%</div>
                <div class="value">
                    <div class="chips-container">
                        <div class="chips"></div>
                        <label class="row middle">
                            <span class="icon small outline">add</span>
                            <input type='text' id="tagInput">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        <button data-action="close" class="flat">%cancel%</button>
        <button data-action="create" class="hover-shadow">%create%</button>
        <button data-action="update" class="hover-shadow" style="display: none;">%update%</button>
    </div>
</div>
`;

export default function taskWindow(btn = null, project = null) {

    let tmp = modalTaskHTML.allReplace(translations);

    let win = Modal.EditorModalWindow.create(tmp);
    let editor = new SimpleMDE(getMDEConfig(win.element.querySelector('textarea')));

    let menu = win.element.querySelector('[data-select=project]');
    let input = win.element.querySelector('[data-input=project]');
    let textField = input.parentElement.parentElement;
    let chipsContainer = win.element.querySelector('.chips-container');
    let chips = chipsContainer.querySelector('.chips');
    let chipInput = chipsContainer.querySelector('input');
    let chipsArray = [];

    
    let btnCreate = win.element.querySelector('[data-action=create]');
    let btnUpdate = win.element.querySelector('[data-action=update]');
    
    let statusField = win.element.querySelector('[data-input=status]');
    let statusValues = win.element.querySelector('[data-select=status]');
    
    let priorityField = win.element.querySelector('[data-input=priority]');
    let priorityValues = win.element.querySelector('[data-select=priority]');

    let endDateInput = win.element.querySelector('input[type=datetime-local]');
    
    if (project) 
    {
        input.dataset.value = project.id;
        input.value = project.name;
        textField.classList.add('active');
    }

    function setPriorityClick(event)
    {
        if (event.target.tagName != 'LI')
            return;
        priorityField.dataset.value = event.target.dataset.value;
        priorityField.value = event.target.innerText;

        event.preventDefault();

        priorityField.blur();
        priorityValues.blur();
    }

    function setStatusClick(event)
    {
        if (event.target.tagName != 'LI')
            return;
        statusField.dataset.value = event.target.dataset.value;
        statusField.value = event.target.innerText;
        
        event.preventDefault();
        
        statusValues.blur();
        statusField.blur();
    }

    priorityValues.addEventListener('click', setPriorityClick);
    statusValues.addEventListener('click', setStatusClick);

    function createOptions() {
        // odstranění třídy active
        textField.classList.remove('active');

        menu.innerHTML = "";
        let options;
        
        // filter
        options = Object.keys(DATA.Projects).reduce(function(filtered,key) {
            if (DATA.Projects[key].name.toLowerCase().includes(input.value.toLowerCase())) 
            {
                filtered[key] = DATA.Projects[key];
            }
            return filtered;
        }, {});
        if (Object.keys(options).length === 0) 
        {
            textField.classList.add('error');
            textField.classList.add('active')
            return;
        } else {
            textField.classList.remove('error');
        }

        for (const [uuid, project] of Object.entries(options)) {
            let item = document.createElement('li');
            item.textContent = project.name;
            item.dataset.projectId = uuid;
            item.onclick = function(e) {
                input.dataset.value = uuid;
                input.value = item.textContent;
                textField.classList.add('active');
                e.preventDefault();
                input.blur();
            }
            menu.appendChild(item);
        }
    }
    createOptions();
    input.addEventListener('input', createOptions);

    chipInput.addEventListener('change', function() {
        let value = chipInput.value.trim();
        if (value == '' || chipsArray.includes(value))
            return;
    
    
        chipsArray.push(value);
        let tmpEl = document.createElement('div');
        tmpEl.innerHTML = `<div class="chip"><span>${value}</span><button class="icon outline flat no-border">close</button></div>`;
        let tmpBtn = tmpEl.getElementsByTagName('button')[0];
        
        tmpBtn.addEventListener('click', function() {
            tmpBtn.parentElement.remove();
            let index = chipsArray.indexOf(value);
            if (index !== -1)
                chipsArray.splice(index, 1);
        });
        chips.appendChild(tmpEl.children[0]);
        chips.scroll({'top': chips.scrollHeight, 'behavior': 'smooth'});
    
        chipInput.value = "";
    });



    win.setParent(document.body.querySelector('main'));

    if (btn) 
    {
        btn.addEventListener('click', function() {
            win.show();
        });
    }

    win.setButtonShow = function(b) 
    {
        b.addEventListener('click', function() {
            win.show();
        });
    }

    win.getNameInput = function() {
        return win.element.querySelector('[data-input=task_name]');
    }

    win.getProjectInput = function() {
        return input;
    };

    win.setProject = function(name, uuid) {
        input.dataset.value = uuid;
        input.value = name;
        textField.classList.add('active');
    }

    win.getEditor = function() {
        return editor;
    }

    win.getChips = function() {
        return chipsArray;
    }

    win.actionCreate = function() {
        btnCreate.style.display = null;
        btnUpdate.style.display = "none";
        input.disabled = false;
    }

    win.actionUpdate = function() {
        btnUpdate.style.display = null;
        btnCreate.style.display = "none";
        input.disabled = true;
    }

    win.setChips = function(tags) {
        chipsArray = tags;
        chipsContainer
        chipsArray.forEach(chip => {
            let tmpEl = document.createElement('div');
            tmpEl.innerHTML = `<div class="chip"><span>${chip}</span><button class="icon outline flat no-border">close</button></div>`;
            let tmpBtn = tmpEl.getElementsByTagName('button')[0];

            tmpBtn.addEventListener('click', function() {
                tmpBtn.parentElement.remove();
                let index = chipsArray.indexOf(value);
                if (index !== -1)
                    chipsArray.splice(index, 1);
            });
            chips.appendChild(tmpEl.children[0]);
        });
    } 

    win.getStatus = function() {
        return statusField.dataset.value;
    }

    win.setStatus = function(intVal) {
        let tmp = statusValues.querySelector(`[data-value='${intVal}']`);
        statusField.dataset.value = tmp.dataset.value;
        statusField.value = tmp.innerHTML;
    }

    win.getPriority = function() {
        return priorityField.dataset.value;
    }

    win.setPriority = function(intVal) {
        let tmp = priorityValues.querySelector(`[data-value='${intVal}']`);
        priorityField.dataset.value = tmp.dataset.value;
        priorityField.value = tmp.innerHTML;
    }

    win.getEndDate = function() {
        return endDateInput.value;
    }

    win.setEndDate = function(date) {
        date.setMinutes(-date.getTimezoneOffset() + 1);
        endDateInput.value = date.toISOString().substr(0,16);
    }

    win.clear = function() {
        input.value = "";
        input.dataset.value = "";

        editor.value("");

        chipInput.value = "";
        chipInput.dataset.value = "";
        chips.innerHTML = "";

        priorityField.value = "";
        priorityField.dataset.value = "";

        endDateInput.value = "";
    }

    win.setActionCreate = function(action) {
        btnCreate.onclick = action;
    }

    win.setActionUpdate = function(action) {
        btnUpdate.onclick = action;
    }

    win.validate = function() 
    {
        //TODO: validace dat
        return true;
    }

    return win;
}