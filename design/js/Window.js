class Window {
    constructor(title, content, parent = document.body) {
        this.__element = document.createElement("div");
        this.__move = false;
        this.__element.className = "window";
        this.__element.style.zIndex = 9;
        this.__rect = this.__element.getBoundingClientRect();
        
        
        this.__element.innerHTML = `
        <div class="head">
        <div class="title">${title}</div>
        <button class="flat text" style="display: none;"><span class="material-icons-round">close</span></button>
        </div>
        <div class="content">${content}</div>`;
        
        
        parent.appendChild(this.__element);
    }


    show(self = this, duration = 300) {
        ANIMATIONS.fadeIn(self.__element, duration);
    }

    close(self = this, duration = 300) {
        ANIMATIONS.fadeOut(self.__element, duration);
    }
    destroy(self = this) {
        self.__element.remove();
    }
    setClose(destroy = true) {
        let self = this;

        this.__element.querySelector(".head > button").style.display = "block";


        if (destroy) {
            this.__element.querySelector(".head > button").onclick = function() {
                self.destroy(self);
            };
        } else {
            this.__element.querySelector(".head > button").onclick = function() {
                self.close(self);
            };
        }
    }
    setMove(move = true) {

        if (this.__move == move) return;

        this.__move = move;
        let head = this.__element.querySelector(".head");
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

            self.__rect = self.__element.getBoundingClientRect();
            moveOffset.x = event.pageX - self.__rect.left;
            moveOffset.y = event.pageY - self.__rect.top;
            self.__element.onmousemove = mouseMoveListener;
            self.__element.style.zIndex = 11;
        }

        function mouseUpListener() {
            self.__element.onmousemove = null;
            self.__element.style.zIndex = 10;
        }

        function mouseMoveListener(event) {
            let posX = event.pageX - moveOffset.x;
            let posY = event.pageY - moveOffset.y;

            self.move(posX, posY);
        }


    }

    move(x,y) {
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
    }


}