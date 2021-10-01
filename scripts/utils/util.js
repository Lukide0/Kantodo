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

function getHeight(el) {
    let compStyle = window.getComputedStyle(el);

    return Math.ceil(el.offsetHeight + parseFloat(compStyle['marginTop']) + parseFloat(compStyle['marginBottom']));
}

export {moveAbs, getHeight}