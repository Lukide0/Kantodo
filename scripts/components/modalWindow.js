import { initInput } from '../utils/util.js';
import Validation from '../utils/validation.js';
import { simpleAnimation } from './../utils/animation.js';

const EditorModalWindow = {
    _template: null,
    _init: true,
    init() {
        let tmp = document.createElement('div');
        tmp.innerHTML = `
        <div class="modal">
            <div class="editor-modal">
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
        if (content != '')
            tmp.querySelector('.editor-modal').innerHTML = content;
        tmp.querySelectorAll(".text-field > .field > input").forEach(el => {initInput(el);});

        tmp.style.visibility = 'hidden';

        let modalWindow = {
            element: tmp,
            isHidden: true,
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
                this.isHidden = false;
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
                {
                    element = self.element;
                    self.isHidden = true;
                }
                else 
                {
                    element = this.element;
                    this.isHidden = true;
                }

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
        if (tmp.querySelector('button[data-action=close]')) 
        {
            tmp.querySelector('button[data-action=close]').addEventListener('click', function() {
                modalWindow.hide(modalWindow);
            });
        }
        return modalWindow;
    }
};
const ModalProject = Object.create(EditorModalWindow);
ModalProject.init = function() {
    this._init = false;
    let tmp = EditorModalWindow.init();
    let content = tmp.querySelector('.editor-modal');
    content.style.flexGrow = "0";
    content.innerHTML = `
    <div class="content" style="max-width: unset">
        <div class="container v-space-around">
            <div class="row">
                <label class="text-field">
                    <div class="field">
                        <span>${translations['%project_name%']}</span>
                        <input type="text" data-value="name">
                    </div>
                    <div class="text"></div>
                </label>
                <button class="hover-shadow space-small-left" style="max-height: 50px;"  data-action='create'>${translations['%create%']}</button>
            </div>
            <div class="row v-space-around space-big" style="font-size: 2rem;color: var(--font-900);font-weight: 900;">${translations['%or%']}</div>
            <div class="row">
                <label class="text-field">
                    <div class="field">
                        <span>${translations['%project_code%']}</span>
                        <input type="text" data-value="code">
                    </div>
                    <div class="text"></div>
                </label>
                <button class="hover-shadow space-small-left success" style="max-height: 50px;" data-action='join'>${translations['%join%']}</button>
            </div>
        </div>
        <div class="row">
            <button data-action='close' class=" right flat space-medium-top" style="margin-left: auto">${translations['%cancel%']}</button>
        </div>
    </div>
    `;
    this._template = tmp;
};

ModalProject.create = function() {
    if (this._template == null || this._init)
        this.init();

    let tmp = EditorModalWindow.create();

    tmp.getName = function() {
        return tmp.element.querySelector('[data-value=name]').value;
    }

    tmp.getCode = function() {
        return tmp.element.querySelector('[data-value=code]').value;
    }

    tmp.clear = function() {
        tmp.element.querySelector('[data-value=name]').value = "";
        tmp.element.querySelector('[data-value=code]').value = "";
    }
    tmp.setActionCreate = function(callback) {
        tmp.element.querySelector('button[data-action=create]').addEventListener('click', function() {
            callback([tmp.getName()]);
        });
    }

    tmp.setActionJoin = function(callback) {
        tmp.element.querySelector('button[data-action=join]').addEventListener('click', function() {
            callback([tmp.getCode()]);
        });
    }

    tmp.setNameError = function(error) {
        tmp.element.querySelector('[data-value=name]').parentNode.parentNode.classList.add('error');
        tmp.element.querySelector('[data-value=name]').parentNode.parentNode.children[1].innerText = error;
    }
    
    tmp.setCodeError = function(error) {
        tmp.element.querySelector('[data-value=code]').parentNode.parentNode.classList.add('error');
        tmp.element.querySelector('[data-value=code]').parentNode.parentNode.children[1].innerText = error;
    }

    tmp.setNameValidation = function(callback)
    {
        let element = tmp.element.querySelector('.text-field input');
        Validation.eager(element, callback, element);
    }

    tmp.clearNameError = function() {
        tmp.element.querySelector('[data-value=name]').parentNode.parentNode.classList.remove('error');
        tmp.element.querySelector('[data-value=name]').parentNode.parentNode.children[1].innerText = "";
    }

    tmp.clearCodeError = function() {
        tmp.element.querySelector('[data-value=code]').parentNode.parentNode.classList.remove('error');
        tmp.element.querySelector('[data-value=code]').parentNode.parentNode.children[1].innerText = "";
    }

    return tmp;
};

export {EditorModalWindow, ModalProject};