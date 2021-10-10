import { Snackbar, Dialog, Banner } from "./components/modal.js";
import { Menu, Item } from "./components/dropdown.js";
import { XHR, Request, CONTENT_TYPE } from "./utils/request.js";
import Editor from "./components/editor.js";

//---- Modal ----//
window.Modal = {
    Snackbar,
    Dialog,
    Banner
};

//---- Dropdown ----//
window.Dropdown = {
    Menu,
    Item
};

//---- Editor ----//
window.MDEditor = Editor;

//---- Request ----//
window.Request = {
    XHR,
    CONTENT_TYPE,
    Request
}
