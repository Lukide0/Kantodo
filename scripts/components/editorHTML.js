import Editor from "./editor.js";

const EditorHTML = {
    _template: null,
    init() {
        let tmp = document.createElement('div');
        tmp.innerHTML = `
        <div class="editor">
            <div class="menu">
                <ul class="group">
                    <li><button class="flat no-border no-padding" data-tooltip="undo" data-action="undo" data-select="none"><span class="icon round">undo</span></button></li>
                    <li><button class="flat no-border no-padding" data-tooltip="redo" data-action="redo"><span class="icon round">redo</span></button></li>
                </ul>
                <ul class="group">
                    <li><button class="flat no-border no-padding" data-tooltip="bold" data-action="bold"><span class="icon round">format_bold</span></button></li>
                    <li><button class="flat no-border no-padding" data-tooltip="italic" data-action="italic"><span class="icon round">format_italic</span></button></li>
                </ul>
                <ul class="group">
                    <li><button class="flat no-border no-padding" data-tooltip="heading" data-action="heading" data-select="none"><span class="icon round">format_size</span></button></li>
                    <li><button class="flat no-border no-padding" data-tooltip="strikethrough" data-action="strikethrough"><span class="icon round">strikethrough_s</span></button></li>
                    <li><button class="flat no-border no-padding" data-tooltip="quote" data-action="quote" data-select="none"><span class="icon round">format_quote</span></button></li>
                </ul>
                <ul class="group">
                    <li><button class="flat no-border no-padding" data-tooltip="list" data-action="list" data-select="none"><span class="icon round">format_list_bulleted</span></button></li>
                    <li><button class="flat no-border no-padding" data-tooltip="checklist" data-action="list" data-select="none"><span class="icon round">checklist</span></button></li>
                </ul>
                <ul class="group">
                    <li><button class="flat no-border no-padding" data-tooltip="code" data-action="code"><span class="icon round">code</span></button></li>
                </ul>
                <button class="space-huge-left mode flat no-border" data-action="switchMode">normal</button>
            </div>
            <div class="editable" contenteditable="true"></div>
        </div>
        `;
        this._template = tmp.children[0];
    },

    create(content = '') 
    {
        if (this._template == null)
            this.init();
        
        let tmp = this._template.cloneNode(true);
        Editor.init(tmp);

        tmp.querySelector('.editable').innerHTML = content;

        let editorObj = {
            element: tmp,
            setContent(c) {
                tmp.querySelector('.editable').innerHTML = c;
            },
            setParent(parent) {
                parent.appendChild(this.element);
            },
            destroy() {
                this.element.remove();
            }
        };

        return editorObj;
    }
};

export default EditorHTML;