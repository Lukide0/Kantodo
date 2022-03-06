const Validation = Object.freeze({
    'agressive': function(element, callback, obj = {}) {
        element.addEventListener('input', function(event) {
            callback(event, obj)
        }, false);
    },
    'lazy': function(element, callback, obj = {}) {
        element.addEventListener('change', function(event) {
            callback(event, obj)
        }, false);
    },
    
    /**
     * Validate in eager mode
     *
     * @param   {HTMLElement}  element  form element
     * @param   {CallableFunction}  callback  validation callback that return bool if is valid value in element
     *
     */
    'eager': function(element, callback, obj = {}) {
        let lazy = true;

        if (!element)
            return;

        element.addEventListener('change', lazyFunc, false);
        function lazyFunc(event) {
            lazy = callback(event, obj);

            if (!lazy)
            {
                element.removeEventListener('change', lazyFunc);
                element.addEventListener('input', agressiveFunc, false);
            }

        }

        function agressiveFunc(event) {
            lazy = callback(event, obj) ?? true;

            if (lazy)
            {
                element.removeEventListener('input', agressiveFunc);
                element.addEventListener('change', lazyFunc);
            }
        }

    }
});

export default Validation;