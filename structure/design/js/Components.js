function switchPasswordVisibility(e) {
    if (e.target.dataset['show'] == 'false') {
        e.target.innerText = 'visibility_off';
        e.target.parentElement.parentElement.getElementsByTagName('input')[0].type = 'text';
        e.target.dataset['show'] = true;
    } else {
        e.target.innerText = 'visibility';
        e.target.dataset['show'] = false;
        e.target.parentElement.parentElement.getElementsByTagName('input')[0].type = 'password';
    }
}

function switchInputDisable(parent, disable = true){
    let els = parent.getElementsByTagName('input');

    for (let index = 0; index < els.length; index++) {
        let el = els[index];
        el.disabled = disable;
    }
}


// create flash message container

const flashMessages = {
    'container': document.createElement('div'),
    'messages': [],
    'notify': function() {
        
    },
    'add': function(flashElement) {

        flashElement.style.bottom = this.__currentOffsetBottom + this.gap + 'px';
        this.container.appendChild(flashElement);

    },
    '__currentOffsetBottom': 5,
    'gap': 5 
};

flashMessages.container.style.position = 'absolute';
flashMessages.container.style.left = 0;
flashMessages.container.style.rigth = 0;
flashMessages.container.style.top = 0;
flashMessages.container.style.bottom = 0;

document.body.appendChild(flashMessages.container);


function FlashMessage(content, lifetime = 3000) {

    // flash message
    let flashMessage = document.createElement('div');
    flashMessage.className = 'flash-message';
    flashMessage.innerHTML = content;


    flashMessages.add(flashMessage);
}
