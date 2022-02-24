import { moveAbs } from "../utils/util.js";

/**
 * Dropdown menu
 */
const Menu = {
    create(items = []) {
        let element = document.createElement('ul');
        element.className = 'dropdown-menu';

        return {
            element,
            items,
            move(x,y) {
                moveAbs({top: y, left: x}, element);
            },
            render() {
                element.innerHTML = "";
                element.setAttribute('tabindex', -1);
                items.forEach(item => {
                    item.render();
                    element.appendChild(item.element);
                }); 
            }
        };
    }
};

/**
 * Item
 */
const Item = {
    /**
     * Vytvoří item
     */
    create(text, action = null,icon = null, items = []) {
        let element = document.createElement('li');
        if (typeof action === 'function')
            element.addEventListener('click', action);

        return {
            element,
            text,
            icon,
            items,
            addAction(callback) {
                if (typeof callback === 'function')
                    this.element.addEventListener('click', callback);
            },
            render() {

                if (this.icon !== null) {
                    // vytvoření ikony
                    this.element.classList.add('icon');
                    let iconEl = document.createElement('span');
                    iconEl.className = ((typeof this.icon.style !== 'undefined') ? this.icon.style : "outline") + " icon medium";
                    iconEl.innerHTML = this.icon.text;
                    this.element.appendChild(iconEl);
                }

                this.element.innerHTML += `<div class="text">${this.text}</div>`;
                
                // pokud máme items 
                if (this.items.length > 0) {
                    this.element.classList.add('dropdown');
                    
                    let tmp = document.createElement('ul');
                    tmp.className = 'dropdown-menu';

                    this.items.forEach(el => {
                        recursiveRender(tmp, el);
                    });

                    this.element.appendChild(tmp);
                }
        
                function recursiveRender(parent,item) {

                    if (item.icon !== null) {
                        // přidání ikony
                        item.element.classList.add('icon');
                        let iconEl = document.createElement('span');
                        iconEl.className = ((typeof item.icon.style !== 'undefined') ? item.icon.style : "outlined") + " icon";
                        iconEl.innerHTML = item.icon.text;
                        item.element.appendChild(iconEl);
                    }
                    item.element.innerHTML += `<div class="text">${item.text}</div>`;
                    
                    if (item.items.length > 0) {
                        item.element.classList.add('dropdown');
                        
                        let tmp = document.createElement('ul');
                        tmp.className = 'dropdown-menu';

                        item.items.forEach(el => {
                            recursiveRender(tmp, el);
                        });

                        item.element.appendChild(tmp);
                    }
        
                    parent.appendChild(item.element);
                }
            }
        };
    }
}


export {Menu, Item};