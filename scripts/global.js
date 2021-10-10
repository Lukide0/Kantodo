import { Snackbar, Dialog, Banner } from "./components/modal.js";
import { XHR, Request, CONTENT_TYPE } from "./utils/request.js";
import Editor from "./components/editor.js";

//---- Modal ----//
window.Modal = {
    Snackbar,
    Dialog,
    Banner
};

//---- Editor ----//
window.MDEditor = Editor;

//---- Request ----//
window.Request = {
    XHR,
    CONTENT_TYPE,
    Request
}