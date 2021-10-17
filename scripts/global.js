import { Snackbar, Dialog, Banner } from "./components/modal.js";
import { Menu, Item } from "./components/dropdown.js";
import { XHR, Request as Action, CONTENT_TYPE } from "./utils/request.js";
import EditorHTML from "./components/editorHTML.js";
import {ModalWindow, ModalProject, ModalTask} from "./components/modalWindow.js";
import Kantodo from "./kantodo.js";
import Validation from "./utils/validation.js";

window.Kantodo = Kantodo;

//---- Modal ----//
window.Modal = {
    Snackbar,
    Dialog,
    Banner,
    ModalWindow,
    ModalProject,
    ModalTask
};

//---- Dropdown ----//
window.Dropdown = {
    Menu,
    Item
};

//---- Editor ----//
window.MDEditor = EditorHTML;

//---- Request ----//
window.Request = {
    XHR,
    CONTENT_TYPE,
    Action
};

//---- Validation ----//
window.Validation = Validation;

Kantodo.info("Globals loaded");