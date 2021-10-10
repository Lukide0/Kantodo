window.addEventListener("load", function() {

    // init text-field
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

    // init menu dropdown
    let dropdowns = document.querySelectorAll("nav > .item.dropdown");
    dropdowns.forEach(el => {
        el.addEventListener('click', function(e){
            let target = e.target;
            
            if (target.parentElement.tagName != 'DIV' && !target.classList.contains('dropdown'))
                return;

            if (el.classList.contains('expanded'))
                el.classList.remove('expanded');
            else
                el.classList.add('expanded');
        });
    });




    
}, {once: true});