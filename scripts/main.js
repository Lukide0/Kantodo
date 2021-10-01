window.addEventListener("load", function() {

    let inputs = document.querySelectorAll(".text-field > .field > input");
   
    inputs.forEach(el => {
        if (el.value != "")
            el.parentElement.classList.add("focus");

        el.addEventListener("change", function() {
            if (el.value == "")
                el.parentElement.classList.remove("focus");
            else if (!el.parentElement.classList.contains('focus'))
                el.parentElement.classList.add("focus");
        });
    });
    
}, {once: true});