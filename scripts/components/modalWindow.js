import { initInput } from '../utils/util.js';
import Validation from '../utils/validation.js';
import { simpleAnimation } from './../utils/animation.js';

const EditorModalWindow = {
    _template: null,
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
        console.log(tmp);
        if (content != '')
            tmp.querySelector('.editor-modal').innerHTML = content;
        tmp.querySelectorAll(".text-field > .field > input").forEach(el => {initInput(el);});

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
        if (tmp.querySelector('button[data-action=close]')) 
        {
            tmp.querySelector('button[data-action=close]').addEventListener('click', function() {
                modalWindow.hide(modalWindow);
            });
        }
        return modalWindow;
    }
};
// TODO: preklady
const ModalProject = Object.create(EditorModalWindow);
ModalProject.init = function() {
    let tmp = EditorModalWindow.init();
    let content = tmp.querySelector('.editor-modal');
    content.innerHTML = `
    <div class="content">
        <div class="container  space-big-bottom">
            <div class="row">
                <label class="text-field">
                    <div class="field">
                        <span>Project name</span>
                        <input type="text">
                    </div>
                    <div class="text"></div>
                </label>
                <button class="hover-shadow space-big-left" style="max-height: 50px;"  data-action='create'>Create</button>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <label class="text-field">
                    <div class="field">
                        <span>Project code</span>
                        <input type="text">
                    </div>
                    <div class="text"></div>
                </label>
                <button class="hover-shadow space-big-left success" style="max-height: 50px;" data-action='join'>Join</button>
            </div>
        </div>
        <div class="row h-space-around space-huge-top">
            <button data-action='close' class=" right flat space-medium-right">Cancel</button>
        </div>
    </div>
    `;
    this._template = tmp;
};

ModalProject.create = function() {
    if (this._template == null)
        this.init();

    let tmp = EditorModalWindow.create();

    tmp.getName = function() {
        return tmp.element.querySelector('.text-field input').value;
    }
    tmp.clear = function() {
        tmp.element.querySelector('.text-field input').value = "";
    }
    tmp.setAction = function(callback) {
        tmp.element.querySelector('button[data-action=create]').addEventListener('click', function() {
            callback([tmp.getName()]);
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

    return tmp;
};

export {EditorModalWindow, ModalProject};