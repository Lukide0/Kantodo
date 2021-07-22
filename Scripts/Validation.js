const ERRORS = Object.freeze({
    'PASSWORD': {
        'MIN_LENGTH': 0,
        'MAX_LENGTH': 1,
        'LOWERCASE_CHAR_COUNT': 2,
        'UPPERCASE_CHAR_COUNT': 3,
        'NUMBERS_COUNT': 4,
        'SPECIAL_CHARS_COUNT': 5
    }
});


function validatePassword(password, opt = {}) 
{
    let length = {
        'min': opt.min ?? 0,
        'max': opt.max ?? Infinity
    };

    let chars = {
        'lowercaseCount': opt.lowercase ?? 0,
        'uppercaseCount': opt.uppercase ?? 0,
        'numberCount': opt.number ?? 0,
        'specialCharCount': opt.specialChar ?? 0
    };

    let errors = [];

    if (password.length < length.min)
        errors.push(ERRORS.PASSWORD.MIN_LENGTH);

    if (password.length > length.max) 
        errors.push(ERRORS.PASSWORD.MAX_LENGTH);
    
    
    let arrayChars = password.split('');

    let track = {
        'lowercase': 0,
        'uppercase': 0,
        'number': 0,
        'specialChar': 0
    };

    const format = /[`!@#$%^&*()_+\-=\[\]{};':'\\|,.<>\/?~]/;

    arrayChars.forEach((char) => {
        if (!isNaN(parseInt(char)))
            track.number++;
        else if (format.test(char))
            track.specialChar++;
        else if (char.toUpperCase() == char)
            track.uppercase++;
        else if (char.toLowerCase() == char)
            track.lowercase++;
    });


    if (chars.lowercaseCount > track.lowercase)
        errors.push(ERRORS.PASSWORD.LOWERCASE_CHAR_COUNT);
    
    if (chars.uppercaseCount > track.uppercase)
        errors.push(ERRORS.PASSWORD.UPPERCASE_CHAR_COUNT);

    if (chars.numberCount > track.number)
        errors.push(ERRORS.PASSWORD.NUMBERS_COUNT);
    
    if (chars.specialCharCount > track.specialChar)
        errors.push(ERRORS.PASSWORD.SPECIAL_CHARS_COUNT);

    return errors;
}

const ValidationMode = Object.freeze({
    'agressive': function(element, callback, obj = {}) {
        callback = window[callback];
        element.addEventListener('input', function(event) {
            callback(event, obj)
        }, false);
    },
    'lazy': function(element, callback, obj = {}) {
        callback = window[callback];
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
        callback = window[callback];

        element.addEventListener('change', lazyFunc, false);
        function lazyFunc(event) {
            lazy = callback(event, obj) ?? true;

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

/* PASSWORD VALIDATION */
window.onload = function() 
{
    let passwordElements = document.querySelectorAll('[data-password-validation]');
    passwordElements.forEach((el) => {
        let func = el.dataset.passwordValidation;
        let requirements = document.querySelector(`[data-password-requirements='${el.name}']`);

        if (requirements != null) 
        {
            let obj = {
                'parent': requirements
            };
            for (let i = 0; i < requirements.children.length; i++) {
                const element = requirements.children[i];
                let error = element.dataset.error ?? '';
                let errorCode = ERRORS.PASSWORD[error] ?? null;
                
                if (errorCode != null)
                    obj[errorCode] = element;

            }
            ValidationMode.agressive(el, func, obj);

        } else 
        {
            ValidationMode.agressive(el, func);
        }
        


    });


    window.onload = null;
}