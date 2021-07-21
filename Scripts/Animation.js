
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
function animationCreate(element, options) {
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

const ANIMATIONS = {
    /**
     *
     * @param   {HTMLElement}  element
     * @param   {int}  dur
     * @param   {Function}  timingFunc
     */
    'fadeIn': function(element, duration, timingFunc = (e) => e) {

        let options = {
            'duration': duration,
            'start': function(element) {
                element.style.display = 'block';
            },
            'end': function(element) {
                element.style.opacity = 1;
            },
            'timing': timingFunc,
            'step': frame
        }

        animationCreate(element, options);

        function frame(element, elapsed, dur) {
            element.style.opacity = elapsed / dur;
        }
    },
    'fadeOut': function(element, duration, timingFunc = (e) => e) {

        let options = {
            'duration': duration,
            'start': function(element) {   element.style.display = 'block'; },
            'end': function(element) { element.style.display = 'none'; },
            'timing': timingFunc,
            'step': frame
        }

        animationCreate(element, options);

        function frame(element, elapsed, dur) {
            element.style.opacity = 1 - elapsed / dur;
        }
    }
}