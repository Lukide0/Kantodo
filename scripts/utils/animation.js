/**
 * Create animation
 *
 * @param   {HTMLElement}  element
 * @param   {{
 *      duration: Number,
 *      start: function(element),
 *      end: function(element),
 *      timing: function(Number): Number,
 *      step: function(element, Number, Number)
 * }}  options  animation options
 */
function complexAnimation(element, options) {
    let start;

    function frame(timestamp) {
        if (start === undefined) {
            start = timestamp;
            options.start(element);
        }
        let elapsed = timestamp - start;
        elapsed = options.timing(elapsed);

        options.step(element,elapsed, options.duration);

        if (elapsed < options.duration) requestAnimationFrame(frame);
        else options.end(element);
    }    
    requestAnimationFrame(frame)
}

function setElementCSS(element,cssObj) {
    let keys= Object.keys(cssObj)
    for (let i = 0; i < keys.length; i++) {
        let key = keys[i];


        let value = cssObj[key];
        element.style[key] = value;
    }
}

function simpleAnimation(element, from, to, timing = '250ms ease') {
    setElementCSS(element, from);
    element.style.transition = `all ${timing}`;
    setElementCSS(element, to);
}

export { complexAnimation, simpleAnimation, setElementCSS };