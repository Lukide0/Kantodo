import { moveAbs, createElementFromTemplate as template } from "../utils/util.js";
import { simpleAnimation } from "../utils/animation.js";


const Snackbar = {
    _container: null,
    _template: null,
    init() {
        this._container = document.createElement('div');
        document.body.append(this._container);
        this._container.style = `
            position: fixed;
            right: 0;
            left: 0;
            top: 5px;
            display: flex;
            gap: 5px;
            flex-direction: column;
            align-items: center;
            pointer-events: none;`;
        moveAbs({top: 5, center: true}, this._container);
        

        this._template = template(`
        <div class="snackbar">
            <p></p>
            <button class="flat no-border"></button>
        </div>
        `);
    },
    create(label, button = null, color = null) {
        if (this._template == null)
            this.init();

        let tmp = this._template.cloneNode(true);
        tmp.querySelector('p').innerHTML = label;

        if (color)
            tmp.classList.add(color);

        tmp.style.visibility = 'hidden';
        this._container.append(tmp);
        const snackbarObj = {
            element: tmp,
            show(visible = 4000, destroy = true) {
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
                if (destroy)
                    setTimeout(this.destroy, visible, this);
                else
                    setTimeout(this.hide, visible, this);
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
            destroy(self) {
                let element;
                if (this.element === undefined)
                    element = self.element;
                else {
                    element = this.element;
                    self = this;
                }

                if (element.style.visibility == 'visible') {
                    self.hide();
                    setTimeout(function() {element.remove()}, 250);
                }

                element.remove();

            }

        };

        let btn = tmp.querySelector('button');
        
        if (button) {
            btn.innerHTML = button.text;
            
            if (typeof button.click === 'function' && button.click !== null)
                btn.onclick = function (e) { button.click(snackbarObj, e) };
        }
        else
            btn.remove();

        return snackbarObj;
    }
}


const Banner = {
    _template: null,

    init() {
        this._template = template(
            `
            <div class="banner">
                <span class="icon round medium"></span>
                <p></p>
                <div class="actions">
                </div>
            </div>`

        );
    },

    create(text, buttons, icon = null, color = 'primary') {
        if (this._template == null)
            this.init();

        let tmp = this._template.cloneNode(true);

        if (icon !== null)
            tmp.querySelector('.icon').innerHTML = icon;

        tmp.querySelector('p').innerHTML = text;

        tmp.style.visibility = 'hidden';
        tmp.style.height = 0;
        tmp.style.overflow = 'hidden';
        tmp.style.padding = 0;

        const bannerObj = {
            element: tmp,

            setParent(parent) {
                parent.insertBefore(this.element, parent.children[0]);
            },

            show() {
                simpleAnimation(
                    this.element,
                    {
                        height: 0,
                        visibility: 'hidden',
                        paddingTop: 0,
                        paddingBottom: 0
                    },
                    {
                        height: 'auto',
                        visibility: 'visible',
                        paddingTop: null,
                        paddingBottom: null
                    }
                );
            },
            hide() {
                simpleAnimation(
                    this.element,
                    {
                        height: 'auto',
                        visibility: 'visible',
                        paddingTop: null,
                        paddingBottom: null
                    },
                    {
                        height: 0,
                        visibility: 'hidden',
                        paddingTop: 0,
                        paddingBottom: 0
                    }
                );
            },
            destroy() {
                if (this.element.style.visibility == 'visible')
                    this.hide();

                this.element.remove();
            }

        }

        let container = tmp.querySelector('.actions');

        buttons.forEach(button => {
            let btn = document.createElement('button');

            btn.className = `flat primary text ${color}`;
            btn.innerHTML = button.text;
            btn.onclick = function (e) {
                button.click(bannerObj, e);
            };

            container.appendChild(btn);
        });

        return bannerObj;
    }
}


const Dialog = {
    _template: null,
    init() {
        this._template = template(
            `
            <div class="dialog">
                <div class="container">
                    <div class="title"></div>
                    <div class="supporting-text"></div>
                    <div class="actions"></div>
                </div>
                </div>
            `
        );
    },

    create(title, text, buttons, color = 'primary', events = null) {
        if (this._template == null)
            this.init();

        let tmp = this._template.cloneNode(true);

        tmp.style.opacity = 0;
        tmp.style.visibility = 'hidden';

        tmp.querySelector('.title').innerHTML = title;
        tmp.querySelector('.supporting-text').innerHTML = text;

        const dialogObj = {
            element: tmp,

            setParent(parent) {
                parent.appendChild(this.element);
                let self = this;

                function hideOnEscape(e) 
                {
                    if (e.key == 'Escape') 
                    {
                        self.destroy();
                        document.removeEventListener('keydown', hideOnEscape);
                    }
                }

                document.addEventListener('keydown', hideOnEscape);
            },
            show() {
                simpleAnimation(
                    this.element,
                    {
                        opacity: 0,
                        visibility: 'hidden'
                    },
                    {
                        opacity: 1,
                        visibility: 'visible'
                    }
                );
            },
            hide() {
                simpleAnimation(
                    this.element,
                    {
                        opacity: 1,
                        visibility: 'visible'
                    },
                    {
                        opacity: 0,
                        visibility: 'hidden'
                    }
                );
            },
            destroy() {
                if (this.element.style.visibility == 'visible')
                    this.hide();
                
                setTimeout(() => { this.element.remove();  }, 250);
            }
        };

        let container = tmp.querySelector('.actions');

        buttons.forEach(button => {
            let btn = document.createElement('button');

            let classes = (typeof button.classList == 'string') ? button.classList : `flat action primary text ${color}`;

            btn.className = classes;
            btn.innerHTML = button.text;
            btn.onclick = function (e) {
                button.click(dialogObj, e);
            };

            container.appendChild(btn);
        });

        tmp.onclick = function (e) {
            if (e.target === tmp) {
                if (events !== null && typeof events === 'object') {
                    if (events.closing !== undefined && typeof events.closing === 'function') {
                        events.closing(dialogObj, e);
                    }
                }
                dialogObj.destroy();
            }
        }

        return dialogObj;
    }
}

export {Snackbar, Banner, Dialog};