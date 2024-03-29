function moveAbs(move = {}, el) {
    let vertical = [], horizontal = [];
 
    if (typeof move.top !== 'undefined' && move.top !== null) 
    {
        vertical = ['top', move.top];
    } else if (typeof move.bottom !== 'undefined' && move.bottom !== null) 
    {
        vertical = ['bottom', move.bottom];
    }

    if (typeof move.center !== 'undefined' && move.center !== null) {
        horizontal = ['left', '50%'];

        if (move.center == true) {
            el.style.transform = "translate(-50%,0)";
        }
    } else if (typeof move.left !== 'undefined' && move.left !== null) 
    {
        horizontal = ['left', move.left];
    } else if (typeof move.right !== 'undefined' && move.left !== null) 
    {
        horizontal = ['right', move.right];
    }


    if (Number.isInteger(horizontal[1])) 
        horizontal[1] = `${horizontal[1]}px`;

    if (Number.isInteger(vertical[1])) 
        vertical[1] = `${vertical[1]}px`;

    el.style[horizontal[0]] = horizontal[1];
    el.style[vertical[0]] = vertical[1];
}

function createElementFromTemplate(html) {
    let tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.children[0];
}

function initInput(el) {
    if (el.value != "")
        el.parentElement.classList.add("focus");

    el.addEventListener("change", function() {
        if (el.value == "")
            el.parentElement.classList.remove("focus");
        else if (!el.parentElement.classList.contains('focus'))
            el.parentElement.classList.add("focus");
    });
}

export {moveAbs, createElementFromTemplate, initInput}