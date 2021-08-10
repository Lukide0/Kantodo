const Column = {
   'create': function(name, parent, offset) {
      let tmpContainer = document.createElement('div');
      tmpContainer.innerHTML = `
      <div class='column'>
            <div class='row'>
               <div class='title'>${name}</div>
               <div class='actions'>
                  <button class='icon-small round flat'>
                        <span class='material-icons-round'>add</span>
                  </button>
                  <button class='icon-small round flat'>
                        <span class='material-icons-round'>more_horiz</span>
                  </button>
               </div>
            </div>
            <div class='tasks' data-drop-area='task'></div>
      </div>`;

      let column = tmpContainer.children[0];

      parent.insertBefore(column, parent.children[parent.children.length - offset]);
      return this.init(column);
   },
   'init': function(element) {

      let buttons = element.getElementsByTagName('button');

      let addTaskBtn = buttons[0];
      let optionsBtn = buttons[1];

      const columnObj = {
         '__element': element,
         '__tasksContainer': element.querySelector('.tasks'),
         '__addTaskBtn': addTaskBtn,
         '__optionsBtn': optionsBtn,
         '__column': element.dataset.column,
         set addTaskAction(callback) {
            let self = this;
            this.__addTaskBtn.onclick = function(event) {
               callback(event, self.__column);
            };
         },
         set optionsAction(callback) {
            let self = this;
            this.__optionsBtn.onclick = function(event) {
               callback(event, self.__column);
            };
         }
      };
      return columnObj;
   }
};
