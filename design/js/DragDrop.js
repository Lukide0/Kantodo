(function() {
    "use strict";

    let element = null;
    let elements = document.querySelectorAll("[data-drop]");
    elements.forEach(el => {
        el.onmousedown = function() {
            el.draggable = true;
        }
    });

    let areas = document.querySelectorAll("[data-drop-area]");

    areas.forEach(area => {area.ondragover = dragOver; area.ondragenter = dragEnter; area.ondrop = drop;} );

    document.addEventListener("dragstart", dragStart);
    document.addEventListener("dragend", dragEnd);

    function dragStart(event) {
        //if (typeof event.target?.dataset["drop"])
        if(typeof event.target?.dataset["drop"] == "undefined") return;
        //if (typeof event.target.dataset["drop"] === "undefined") return;
        console.log("DRAG: START")
        element = event.target;
        event.dataTransfer.effectAllowed = "move";
        setTimeout(function(){ element.style.opacity = 0.5}, 0);
    }

    function dragEnd(event) {
        if (!element) return;

        console.log("DRAG: END");
        setTimeout(function(){element.style.opacity = 1;element.draggable = false;element = null; },0);
    }

    function drop(event) {
        // socket msg
    }

    function dragEnter(event) {
        if (!element) return;

        let area = this;
        let target = event.target;

        event.preventDefault();


        if (area === target) {
            area.appendChild(element);
            return;
        }

        let item = target;
        let loop = 100;
        let i = 0;
        while(i < loop) {
            if (item.parentElement === area) break;
            item = item.parentElement;
        }

        if (item === element) return;

        item.style.transition = "all 150ms ease";
        element.style.height = "0px";
        area.insertBefore(element, item);
        setTimeout(function() {
            element.style.height = "initial"
        },0);

        // 
        //transition all 150ms ease





    }
    
    function dragOver(event) {
        if (!element) return;
        let area = this;
        const isValidArea = area.dataset["dropArea"] === element.dataset["drop"];
        
        if (isValidArea)  {
            event.preventDefault();
            event.dataTransfer.dropEffect = "move";
        }
    }

    window.dragDrop = {
        'addArea': function(area) {
            area.ondragover = dragOver;
            area.ondragenter = dragEnter;
            area.ondrop = drop;
        }
    }

})();