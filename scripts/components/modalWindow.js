import { simpleAnimation } from './../utils/animation.js';

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
        </label>
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
            <button class="space-huge-left mode flat no-border" data-action="switchMode">normal mode</button>
        </div>
        <div class="editable" contenteditable="true">
            
        </div>
    </div>
    <div class="row right space-big-top">
        <button class="flat space-medium-right">Cancel</button>
        <button class="hover-shadow">Create</button>
    </div>
    `;

    this._template = tmp;
}

export {ModalWindow, ModalProject};