const Editor = {
    init(editorElement) 
    {
        const editor = editorElement;

        let editable = editor.getElementsByClassName('editable');
        if (editable.length == 0) 
            throw "Editor does not contain element with class 'editable'";

        editable = editable[0];

        let menu = editor.getElementsByClassName('menu');
        if (menu.length == 0) 
            throw "Editor does not contain element with class 'menu'";
        // TODO: disable on preview mode
        menu = menu[0];

        const editControls = menu.querySelectorAll('button[data-action]');

        let historyStack = [];
        let historyIndex = -1;
        editable.setAttribute("style", "white-space: pre");

        function addToEditorHistory(after, start, length, before = '') {
            historyIndex++;
        
            if (historyIndex == 0 && historyStack.length > 0) 
            {
                historyStack = [];
            }
        
            if (historyStack.length - 1 > historyIndex) 
            {
                historyStack = historyStack.splice(0,historyIndex);
            }
            historyStack.push({before, start, length, after});
        }
        
        editable.addEventListener("keydown", function(e) {
            let range = window.getSelection().getRangeAt(0);
            let start = range.startOffset;
            let end = range.endOffset;
        
            if (e.key == 'Backspace' && (end - start > 0 || start > 0)) {
                addToEditorHistory('', start - 1, 0, editable.textContent.substring(start - 1, end));
            } else if (e.key == 'Delete' && (end - start > 0 || start < editable.textContent.length - 1)) {
                addToEditorHistory('', start, 0, editable.textContent.substring(start, end + 1));
            }

            
        });
        
        editable.addEventListener("keypress", function(e) {
            let start = window.getSelection().getRangeAt(0).startOffset;
            let key = e.key;
        
            // TODO: newline
            if (e.key == 'Enter') {
                key = "\n";
            }
        
            addToEditorHistory(key, start, 1);
        });
        
        const filters = [
            function bold(text) {
                const regex = /\*\*([^\*]+)?(\*\*)/gm;
                return text.replaceAll(regex, '<span class="bold">$1</span>');
            },
            function italic(text) {
                const regex = /\*([^\*]{2,})\*/gm;
                return text.replaceAll(regex, '<span class="italic">$1</span>'); 
            },
            function heading(text) {
                const regex = /^\#\#\#\s(.*)$/gm;
                return text.replaceAll(regex, '<span class="heading">$1</span>');
            },
            function strikethrough(text) {
                const regex = /\~\~([^\~]+)?(\~\~)/gm;;
                return text.replaceAll(regex, '<span class="strikethrough">$1</span>');
            },
            function quote(text) {
                const regex = /^\>\s(.*)$/gm;
                return text.replaceAll(regex, '<span class="quote">$1</span>');
            },
            function list(text) {
                const regex = /^\-\s.+(\s*\-\s.+)*$/gm;
                let match;
                let plusOffset = 0;
                let textCopy = text;
                while((match = regex.exec(text)) !== null) {
                    let tmp = "<ul>";
                    let lines = match[0].split('\n');
                    let currentIndent = 0;
                    
                    for (let i = 0; i < lines.length; i++) {
                        let line = lines[i];
                        let indent = 0;
        
        
                        if (line[0] == ' ' || line[0] == '\t') 
                        {
                            let index = 0;
                            while (index < line.length && (line[index] == ' ' || line[index] == '\t')) {
                                if (currentIndent >= indent) {
                                    indent++;
                                }
                                index++;
                            }
        
                            line = line.substr(index);
                        }
                        if (indent > currentIndent) {
        
                            tmp = tmp.substr(0, tmp.length - 5) + '<ul>';
                            currentIndent++;
                        }
                        else if (indent < currentIndent) {
                        
                            while (currentIndent > indent) {
                                tmp += '</ul></li>';
                                currentIndent--;
                            }
                        }
        
                        line = line.substr(2);
                        
                        const regexCheckbox = /^\[( |[Xx])\]\s/;
        
                        if (regexCheckbox.test(line)) {
                            let classList = "checkbox";
                            if (line[1].toLowerCase() == 'x') {
                                classList += " checked";
                            }
                            line = `<span class='${classList}'></span><span>${line.substr(3)}</span>`;
        
                        }
                        
                        tmp += `<li>${line}</li>`;                   
                    }
        
                    tmp += '</ul>';
        
                    textCopy = textCopy.substring(0, match.index + plusOffset) + tmp + textCopy.substring(match.index + plusOffset + match[0].length);
                    plusOffset += tmp.length - match[0].length;
                }
                return textCopy;
            },
            // TODO: fix element in code
            function code(text) {
                const regex = /\`\`\`([^\`]+)?(\`\`\`)/gm;
                return text.replaceAll(regex, '<span class="code">$1</span>');
            }
        
        ];
        
        let tmpText = "";

        const actions = {
            switchMode: function() {
                if (typeof editable.dataset.mode !== 'undefined' && editable.dataset.mode == 'preview') 
                {
                    editable.dataset.mode = 'markdown';
                    editable.innerHTML = tmpText;
                    editable.contentEditable = true;
                    return;
                }

                editable.dataset.mode = 'preview';
                tmpText = editable.textContent;

                let renderText = tmpText;
                filters.forEach(fn => { renderText = fn(renderText);  });
                editable.contentEditable = false;
                editable.innerHTML = renderText;
            },

            undo: function() {
                if (historyIndex < 0) {
                    return;
                }
                let lastAction = historyStack[historyIndex];
                let text = editable.textContent;
                editable.textContent = text.substring(0, lastAction.start) + lastAction.before + text.substring(lastAction.start + lastAction.length); 
                historyIndex--;
            },
            redo: function() {
                if (historyStack.length - 1 == historyIndex) 
                    return;
        
                let lastAction = historyStack[historyIndex+1];
                let text = editable.textContent;
                editable.textContent = text.substring(0, lastAction.start) + lastAction.after + text.substring(lastAction.start + lastAction.before.length); 
        
                historyIndex++;
            },
            bold: function(text) {
                if (this._isWrapper(text, '*', 0) && this._isWrapper(text, '*', 1)) {
                    return this._return(text.substring(2, text.length - 2));
                } 
                return this._return(`**${text}**`, 2, text.length + 2);
            },
            italic: function(text) {
                if (this._isWrapper(text, '*', 0)) {
                    if (this._isWrapper(text, '*', 1)) 
                    {
                        if (this._isWrapper(text, '*', 2)) 
                        {
                            return this._return(text.substring(2, text.length - 2), 2, text.length + 2);
                        }
                        return this._return(`*${text}*`, 3, text.length + 3);
                    }
                    return this._return(text.substring(1, text.length - 1));
                }
        
                return this._return(`*${text}*`, 1, text.length + 1);
            },
            heading: function() {
                return this._return(`###`, 3, 3);
            },
            strikethrough: function(text) {
                if (this._isWrapper(text, '~', 0) && this._isWrapper(text, '~', 1)) 
                {
                    return this._return(text.substring(2, text.length - 2));
                }
        
                return this._return(`~~${text}~~`, 2, text.length + 2);
            },
            quote: function() {
                return this._return(">\t", 2, 2);
            },
            list: function(text) {
                return this._return("- ", 2, 2);
            },
            listCheck: function(text) {
                return this._return("- []", 5, 5);
            },
            code: function(text) {
                if (this._isWrapper(text, '`', 0) && this._isWrapper(text, '`', 1) && this._isWrapper(text, '`', 2)) {
                    return this._return(text.substring(3, text.length - 3));
                } 
                return this._return(`\`\`\`\n${text}\n\`\`\``, 3, text.length + 3);
            },
            _isWrapper: function(text, char, offset) {
                if (offset >= text.length) {
                    return false;
                }
                return (text[offset] == char && text[text.length-offset - 1] == char);
            },
            _return: function(text, startOffset = 0, endOffset = 0) {
                return {text, startOffset, endOffset};
            }
        };
        
        
        
        
        for (let index = 0; index < editControls.length; index++) {
            const btn = editControls[index];
            btn.addEventListener("click", function() {
                
                let btnAction = btn.dataset.action;
        
                if (btnAction == "undo" || btnAction == "redo" || btnAction == 'switchMode') {
                    actions[btnAction]();
                    return;
                }


                let selection = window.getSelection();
        
                if (selection.focusNode == null)
                    return;
                    
                let range = selection.getRangeAt(0);
        
        
                if (range.toString().length == 0 && btn.dataset.select != 'none')
                    return;
        
        
                const text = range.toString();
                const obj = actions[btn.dataset.action](text)
        
                const newText = obj.text;
                const newNode = document.createTextNode(newText);
        
                range.deleteContents();
                range.insertNode(newNode);
        
                let startOffset = 0;
        
                let index = 0;
        
                while (editable.childNodes[index] != newNode) {
                    startOffset += editable.childNodes[index].textContent.length;
                    index++;
                }
        
                addToEditorHistory(obj.text, startOffset, obj.text.length, text);
                
                editable.focus();
                range.setStart(newNode, obj.startOffset);
                range.setEnd(newNode, obj.endOffset);
            });
        }
    }
}

export default Editor;