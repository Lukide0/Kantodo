import { Snackbar, Dialog, Banner } from "./components/modal.js";
import { Menu, Item } from "./components/dropdown.js";
import { XHR, Request, CONTENT_TYPE } from "./utils/request.js";
import EditorHTML from "./components/editorHTML.js";
import ModalWindow from "./components/modalWindow.js";
import Kantodo from "./kantodo.js";

window.Kantodo = Kantodo;

//---- Modal ----//
window.Modal = {
    Snackbar,
    Dialog,
    Banner,
    ModalWindow
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
    Request
};

Kantodo.info("Globals loaded");