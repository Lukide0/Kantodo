window.addEventListener("load", function() {

    // init text-field
    let inputs = document.querySelectorAll(".text-field > .field > input");
   
    inputs.forEach(el => {
        el.addEventListener("change", function() {
            if (el.value == "")
                el.parentElement.classList.remove("focus");
            else
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