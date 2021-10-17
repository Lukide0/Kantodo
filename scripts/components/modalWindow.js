import { initInput } from '../utils/util.js';
import Validation from '../utils/validation.js';
import { simpleAnimation } from './../utils/animation.js';
import Editor from './editor.js';

const ModalWindow = {
    _template: null,
    init() {
        let tmp = document.createElement('div');
        tmp.innerHTML = `
        <div class="modal">
            <div class="editor-modal">
                <div class="content"></div>
            </div>
        </div>
        `;
        return this._template = tmp.children[0];
    },

    create(content = '') 
    {
        if (this._template == null)
            this.init();
        
        let tmp = this._template.cloneNode(true);

        tmp.querySelectorAll(".text-field > .field > input").forEach(el => initInput(el));

        if (content != '')
            tmp.querySelector('.content').innerHTML = content;
        tmp.style.visibility = 'hidden';

        let modalWindow = {
            element: tmp,
            setContent(c) {
                tmp.querySelector('.content').innerHTML = c;
            },
            getContentEl() {
                return tmp.querySelector('.content');
            },
            getContainer() {
                return tmp.querySelector('.editor-modal');
            },
            setParent(parent) {
                parent.appendChild(this.element);
            },
            show() {
                simpleAnimation(
                    this.element,
                    {
                        opacity: 0,
                        visibility: 'hidden',
                    },
                    {
                        opacity: 1,
                        visibility: 'visible',
                    }
                );
            },
            hide(self) {
                let element;
                if (this.element === undefined)
                    element = self.element;
                else
                    element = this.element;

                simpleAnimation(
                    element,
                    {
                        opacity: 1,
                        visibility: 'visible',
                    },
                    {
                        opacity: 0,
                        visibility: 'hidden',
                    }
                );
            },
            destroy() {
                if (this.element.style.visibility == 'visible')
                    this.hide();

                this.element.remove();
            }
        };

        return modalWindow;
    }
};

const ModalProject = Object.create(ModalWindow);
ModalProject.init = function() {
    let tmp = ModalWindow.init();
    tmp.querySelector('.content').innerHTML = `
    <div class="title">
        <label class="text-field">
            <div class="field">
                <span>Project name</span>
                <input type="text">
            </div>
            <div class="text"></div>
        </label>
    </div>
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
            <button class="space-huge-left mode flat no-border" data-action="switchMode">normal</button>
        </div>
        <div class="editable" contenteditable="true"></div>
    </div>
    <div class="row right space-big-top">
        <button data-action='close' class="flat space-medium-right">Cancel</button>
        <button class="hover-shadow" data-action='create'>Create</button>
    </div>
    `;
    this._template = tmp;
};

ModalProject.create = function() {
    if (this._template == null)
        this.init();

    let tmp = ModalWindow.create();

    
    tmp.getName = function() {
        return tmp.element.querySelector('.text-field input').value;
    }
    
    tmp.getDescription = function() {
        return tmp.element.querySelector('.editable').textContent;
    }

    tmp.clear = function() {
        tmp.element.querySelector('.editable').textContent = "";
        tmp.element.querySelector('.text-field input').value = "";
    }

    tmp.getData = function() {
        return [tmp.element.querySelector('.text-field input').value, tmp.element.querySelector('.editable').textContent];
    }
    tmp.setAction = function(callback) {
        tmp.element.querySelector('button[data-action=create]').addEventListener('click', function() {
            callback(tmp.getData());
        });
    }

    tmp.setNameError = function(error) {
        tmp.element.querySelector('.text-field').classList.add('error');
        
        tmp.element.querySelector('.text-field > .text').innerText = error;
    }

    tmp.setNameValidation = function(callback)
    {
        let element = tmp.element.querySelector('.text-field input');
        Validation.eager(element, callback, element);
    }

    tmp.clearNameError = function() {
        tmp.element.querySelector('.text-field').classList.remove('error');
        tmp.element.querySelector('.text-field > .text').innerText = "";
    }

    Editor.init(tmp.element.querySelector('.editor'));

    tmp.element.querySelector('button[data-action=close]').addEventListener('click', function() {
        tmp.hide(tmp);
    });

    return tmp;
};


const ModalTask = Object.create(ModalWindow);
ModalTask.init = function() {
    let tmp = ModalWindow.init();
    tmp.querySelector('.editor-modal').innerHTML = `
    <div class="content">
        <label class="text-field">
            <div class="field">
                <span>Project name</span>
                <input type="text">
            </div>
            <div class="text"></div>
        </label>
        <div class="row space-big-top">
            <button class="flat">Edit</button>
            <button class="flat space-medium-left no-border">Preview</button>
        </div>
        <div class="editor" id="editor">
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
                    <button class="flat action">Add task</button><span>or</span><button class="flat action">Create task</button>
                </div>
            </div>
        </div>
        <div class="actions">
            <button class="flat">Attachment</button>
        </div>
    </div>
    <div class="settings">
        <button class="flat no-padding">
            <span class="icon outline colored">dashboard</span><p class="space-regular-right">Select a project</p>
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
            <button class="flat">Cancel</button>
            <button class="hover-shadow">Create</button>
        </div>
    </div>`;
    this._template = tmp;
};


ModalTask.create = function() {
    if (this._template == null)
        this.init();

    let tmp = ModalWindow.create();

    
    tmp.getName = function() {
        return tmp.element.querySelector('.text-field input').value;
    }
    
    tmp.getDescription = function() {
        return tmp.element.querySelector('.editable').textContent;
    }

    tmp.clear = function() {
        tmp.element.querySelector('.editable').textContent = "";
        tmp.element.querySelector('.text-field input').value = "";
    }

    tmp.getData = function() {
        return [tmp.element.querySelector('.text-field input').value, tmp.element.querySelector('.editable').textContent];
    }
    tmp.setAction = function(callback) {
        tmp.element.querySelector('button[data-action=create]').addEventListener('click', function() {
            callback(tmp.getData());
        });
    }

    tmp.setNameError = function(error) {
        tmp.element.querySelector('.text-field').classList.add('error');
        
        tmp.element.querySelector('.text-field > .text').innerText = error;
    }

    tmp.setNameValidation = function(callback)
    {
        let element = tmp.element.querySelector('.text-field input');
        Validation.eager(element, callback, element);
    }

    tmp.clearNameError = function() {
        tmp.element.querySelector('.text-field').classList.remove('error');
        tmp.element.querySelector('.text-field > .text').innerText = "";
    }

    Editor.init(tmp.element.querySelector('.editor'));

    tmp.element.querySelector('button[data-action=close]').addEventListener('click', function() {
        tmp.hide(tmp);
    });

    return tmp;
};




export {ModalWindow, ModalProject, ModalTask};