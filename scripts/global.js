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

//---- SimpleMDE config ----//

window.getMDEConfig = function(element = null) 
{
    return  {
        element,
        previewClass: ['markdown-body', 'padding-medium'],
        renderingConfig: {
            codeSyntaxHighlighting: true,
        },
        tabSize: 4,
        spellChecker: false,
        toolbar: [
            'bold',
            'italic',
            'strikethrough',
            '|',
            'heading-1',
            'heading-2',
            'heading-3',
            '|',
            'quote',
            'link',
            'table',
            '|',
            'unordered-list',
            'ordered-list',
            '|',
            'preview',
            'guide'
        ]
    }
}

Kantodo.info("Globals loaded");