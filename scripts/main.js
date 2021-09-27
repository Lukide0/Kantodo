window.addEventListener("load", function() {

    let inputs = document.querySelectorAll(".text-field > .field > input");
   
    inputs.forEach(el => {
        el.addEventListener("change", function() {
            if (el.value == "")
                el.parentElement.classList.remove("focus");
            else
                el.parentElement.classList.add("focus");
        });
    });
    
}, {once: true});