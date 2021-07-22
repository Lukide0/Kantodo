const windowsContainer = document.createElement('div');

window.onload = function() 
{
    let style = windowsContainer.style;

    style.position = 'absolute';
    style.left = 0;
    style.right = 0;
    style.top = 0;
    style.bottom = 0;
    document.body.append(windowsContainer);
}


let windows_count = 0;
let window_last = null;


function Window(title, content) {
    let __element = document.createElement('div');
    let __move = false;
    __element.className = 'window';
    __element.style.zIndex = 999;
    let __rect = __element.getBoundingClientRect();   

    window_last = __element;

    __element.innerHTML = `
        <div class='head'>
        <div class='title'>${title}</div>
        <button class='flat text' style='display: none;'><span class='material-icons-round'>close</span></button>
        </div>
        <div class='content'>${content}</div>
    `;

    __element.style.left = '25%';
    __element.style.top = '50%';

    windowsContainer.appendChild(__element);

    return {
        __element,
        __move,
        __rect,
        '$': function(query) 
        {
            return __element.querySelectorAll(`.content ${query}`);
        },
        'show': function(duration = 300) 
        {
            if (this.onShow !== null)
                this.onShow(this, duration);
            this.isOpened = true;
            ANIMATIONS.fadeIn(this.__element, duration);
        },
        'close': function(duration = 300) {
            if (this.onClose !== null)
                this.onClose(this, duration);

            this.isOpened = false;
            ANIMATIONS.fadeOut(this.__element, duration);
        },
        'destroy': function() 
        {
            if (this.onDestroy !== null)
                this.onDestroy(this);
            
            this.isOpened = false;
            this.__element.remove();
        },
        'setClose': function(destroy = true) 
        {
            let self = this;

            this.__element.querySelector('.head > button').style.display = 'block';
    
    
            if (destroy) {
                this.__element.querySelector('.head > button').onclick = function() {
                    self.destroy(self);
                };
            } else {
                this.__element.querySelector('.head > button').onclick = function() {
                    self.close(self);
                };
            }
        },
        'setMove': function(move = true) 
        {
    
            if (this.__move == move) return;
    
            this.__move = move;
            let head = this.__element.querySelector('.head');
            let self = this;
            let moveOffset = {
                x: 0,
                y: 0
            }
        
            if (move) {
                head.onmousedown = mouseDownListener;
                head.onmouseup =  mouseUpListener;
            } else {
                head.onmousedown = null;
    
            }

            function mouseDownListener(event) {
                if (self.__element != window_last) 
                {
                    window_last.parentNode.insertBefore(self.__element, window_last.nextSibling);
                    window_last = self.__element;
                }

                self.__rect = self.__element.getBoundingClientRect();
                moveOffset.x = event.pageX - self.__rect.left;
                moveOffset.y = event.pageY - self.__rect.top;
                self.__element.onmousemove = mouseMoveListener;
            }
    
            function mouseUpListener() {
                self.__element.onmousemove = null;
            }
    
            function mouseMoveListener(event) {
                let posX = event.pageX - moveOffset.x;
                let posY = event.pageY - moveOffset.y;
    
                self.move(posX, posY);
            }
    
    
        },
        'move': function(x,y) {
            if (x + this.__rect.width >= this.__element.parentElement.offsetWidth) {
                x = this.__element.parentElement.offsetWidth - this.__rect.width;
            } else if (x <= 0) {
                x = 0;
            }
            
            if (y + this.__rect.height >= this.__element.parentElement.offsetHeight) {
                y = this.__element.parentElement.offsetHeight - this.__rect.height;
            } else if (y <= 0) {
                y = 0;
            }
    
            this.__element.style.left = `${x}px`;
            this.__element.style.top = `${y}px`;
        },
        'onClose': null,
        'onShow': null,
        'onDestroy': null,
        'isOpened': false
    };
}