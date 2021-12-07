import { Snackbar, Dialog, Banner } from "./components/modal.js";
import { Menu, Item } from "./components/dropdown.js";
import { XHR, Request as Action, CONTENT_TYPE } from "./utils/request.js";
import { EditorModalWindow, ModalProject } from "./components/modalWindow.js";
import taskWindow from "./components/taskWindow.js";
import Kantodo from "./kantodo.js";
import Validation from "./utils/validation.js";


String.prototype.allReplace = function(obj) {
    var retStr = this;
    for (var x in obj) {
        retStr = retStr.replace(new RegExp(x, 'g'), obj[x]);
    }
    return retStr;
};


window.Kantodo = Kantodo;

//---- Modal ----//
window.Modal = {
    Snackbar,
    Dialog,
    Banner,
    EditorModalWindow,
    ModalProject,
    createTaskWindow: taskWindow,
};

//---- Dropdown ----//
window.Dropdown = {
    Menu,
    Item
};

//---- Request ----//
window.Request = {
    XHR,
    CONTENT_TYPE,
    Action
};

//---- Validation ----//
window.Validation = Validation;

Kantodo.info("Globals loaded");