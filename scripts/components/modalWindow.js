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
}

export {ModalWindow, ModalProject};