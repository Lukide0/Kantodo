function moveAbs(x,y, el) {
    if (Number.isInteger(x)) 
        x = `${x}px`;

    if (Number.isInteger(y)) 
        y = `${y}px`;

    el.style.top = y;
    el.style.left = x;
}

function getHeight(el) {
    let compStyle = window.getComputedStyle(el);

    return Math.ceil(el.offsetHeight + parseFloat(compStyle['marginTop']) + parseFloat(compStyle['marginBottom']));
}

export {moveAbs, getHeight}