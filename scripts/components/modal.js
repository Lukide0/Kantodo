import { moveAbs } from "../utils/util.js";
import { simpleAnimation } from "../utils/animation.js";


const Snackbar = {
    _template: null,
    init() {
        let tmp = document.createElement('div');
        tmp.innerHTML = `
        <div class="snackbar">
            <p>Text label</p>
            <button class="flat no-border">Retry</button>
        </div>
        `;
        this._template = tmp.children[0];
    },
    create(label, button, color = 'primary') {
        if (this._template == null)
            this.init();

        let tmp = this._template.cloneNode(true);
        tmp.querySelector('p').innerHTML = label;

        tmp.style.visibility = 'hidden';

        const snackbarObj = {
            element: tmp,
            setParent(parent) {
                parent.appendChild(this.element);
            },

            show(x, y, visible = 4000) {
                moveAbs(x, y, this.element);
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
            destroy() {
                if (this.element.style.visibility == 'visible')
                    this.hide();

                this.element.remove();
            }

        };

        let btn = tmp.querySelector('button');
        btn.innerHTML = button.text ?? "";

        if (typeof button.click === 'function' && button.click !== null)
            btn.onclick = function (e) { button.click(snackbarObj, e) };
        btn.classList.add(color);

        return snackbarObj;
    }
}


const Banner = {
    _template: null,

    init() {
        let tmp = document.createElement('div');

        tmp.innerHTML = `
        <div class="banner">
            <span class="icon round medium"></span>
            <p></p>
            <div class="actions">
            </div>
        </div>`;

        this._template = tmp.children[0];
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
        let tmp = document.createElement('div');

        tmp.innerHTML = `
        <div class="dialog">
            <div class="container">
                <div class="title"></div>
                <div class="supporting-text"></div>
                <div class="actions"></div>
            </div>
            </div>`;

        this._template = tmp.children[0];
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

                this.element.remove();
            }
        };

        let container = tmp.querySelector('.actions');

        buttons.forEach(button => {
            let btn = document.createElement('button');

            btn.className = `flat primary text ${color}`;
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
                simpleAnimation(tmp, { opacity: 1, visibility: 'visible' }, { opacity: 0, visibility: 'hidden' });
            }
        }

        return dialogObj;
    }
}

export {Snackbar, Banner, Dialog};