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
    let els = parent.getElementsByTagName("input");

    for (let index = 0; index < els.length; index++) {
        let el = els[index];
        el.disabled = disable;
    }
}