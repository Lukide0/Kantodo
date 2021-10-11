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
        this._template = tmp.children[0];
    },

    create(content = '') 
    {
        if (this._template == null)
            this.init();
        
        let tmp = this._template.cloneNode(true);

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

const ModalProject = ModalWindow;

export default ModalWindow;