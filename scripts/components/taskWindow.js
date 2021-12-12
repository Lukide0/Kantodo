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
    <div class="actions">
        <button class="flat">%attachment%</button>
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
                <div class="name">Tags</div>
                <div class="value">
                    <div class="chips-container">
                        <div class="chips"></div>
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
        <button data-action="close" class="flat">%cancel%</button>
        <button data-action="create" class="hover-shadow">%create%</button>
    </div>
</div>
`;

export default function taskWindow(btn, project = null) {

    let tmp = modalTaskHTML.allReplace(translations);

    let win = Modal.EditorModalWindow.create(tmp);
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
    let chipsContainer = win.element.querySelector('.chips-container');
    let chips = chipsContainer.querySelector('.chips');
    let chipInput = chipsContainer.querySelector('input');
    let chipsArray = [];

    if (project) 
    {
        input.dataset.value = project.id;
        input.value = project.name;
        textField.classList.add('active');
    }


    function createOptions(removeActive = true) {
        if (input.value.length != 0 && removeActive)
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
            }
            menu.appendChild(item);
        });
    }
    createOptions(false);

    chipInput.addEventListener('change', function() {
        let value = chipInput.value.trim();
        if (value == '' || chipsArray.includes(value))
            return;
    
    
        chipsArray.push(value)
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
    
    
        chipInput.value = "";
    });
    input.addEventListener('input', createOptions);
    input.addEventListener('click', createOptions);


    win.setParent(document.body.querySelector('main'));

    btn.addEventListener('click', function(e) {
        win.show();
    });

    win.getProjectInput = function() {
        return input;
    };

    win.getEditor = function() {
        return editor;
    }

    win.getChips = function() {
        return chipsArray;
    }

    return win;
}