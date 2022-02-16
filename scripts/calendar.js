const daysContainer = document.querySelector(".days");

var calendarTasks = {};
var loadedProjects = {};

function mapMonth() 
{
    switch(month) 
    {
    case 0:
        return translationCalendar['%january%'];
    case 1:
        return translationCalendar['%february%'];
    case 2:
        return translationCalendar['%march%'];
    case 3:
        return translationCalendar['%april%'];
    case 4:
        return translationCalendar['%may%'];
    case 5:
        return translationCalendar['%june%'];
    case 6:
        return translationCalendar['%july%'];
    case 7:
        return translationCalendar['%august%'];
    case 8:
        return translationCalendar['%september%'];
    case 9:
        return translationCalendar['%october%'];
    case 10:
        return translationCalendar['%november%'];
    case 11:
        return translationCalendar['%december%'];
    }

}

function loadMonth(year, month, currentDate) {
    daysContainer.innerHTML = "";

    setIfNotExist(calendarTasks, [year, month], {});

    const currDay = currentDate.getDate();
    const currMonth = currentDate.getMonth();
    const currYear = currentDate.getFullYear();

    let showToday = year == currYear && month == currMonth;

    const monthDays = new Date(year, month, 0).getDate();
    const previousMonthDays = new Date(year, month - 1, 0).getDate();
    let empty = 7 * 5 - monthDays;

    let startEmpty = Math.floor(empty / 2);
    let endEmpty = Math.ceil(empty / 2);

    for (let i = 1; i <= startEmpty; i++) {
        daysContainer.innerHTML += `
        <div class="day previus">
            <div class="name">${previousMonthDays - startEmpty + i}</div>
        </div>`;
    }

    for (let i = 1; i <= monthDays; i++) {
        daysContainer.innerHTML += `
        <div data-date="${i}" class="day" data-count="0" onclick="showTasks(event, this)">
            <div class="name">${i}</div>
            <div class="tasks-count">0</div>
        </div>`;
    }

    for (let i = 1; i <= endEmpty; i++) {
        daysContainer.innerHTML += `
        <div class="day previus">
            <div class="name">${i}</div>
        </div>`;
    }

    if (showToday)
        document.querySelector(`[data-date='${currDay}']`).classList.add('today');
}

function showTasks(e, el) 
{
    let day = el.dataset['date'];
    let container = document.createElement('div');
    for (const uuid in calendarTasks[year][month]) 
    {
        if (calendarTasks[year][month][uuid][day] !== undefined 
            && calendarTasks[year][month][uuid][day].length > 0) 
        {
            let projContainer = document.createElement('div');
            
            console.log(calendarTasks[year][month][uuid][day]);
            calendarTasks[year][month][uuid][day].forEach(task => {

                DATA.AfterTaskAdd(uuid, task, projContainer, day, function(action, taskID, day) {
                    switch(action) 
                    {
                    case 'remove':
                    case 'complete':
                        break;
                    default:
                        return;
                    }
                    document.querySelector(`[data-task-id='${taskID}']`).remove();
                    let dayEl = document.querySelector(`[data-date='${day}']`);
                    console.log(day, dayEl)
                    dayEl.querySelector('.tasks-count').innerHTML = --dayEl.dataset['count'];
                });
            });

            container.appendChild(projContainer);
        }
    }
    
    let dialog = Modal.Dialog.create(
        `${day}. ${mapMonth(month)}`,
        container.innerHTML,
        [
            {
                'text': translations['%close%'], 
                'classList': 'flat no-border',
                'click': function(dialogOBJ) {
                    dialogOBJ.destroy(true);
                    return false;
                }
            }
        ]
    );
    dialog.setParent(document.body);
    dialog.show();
}

// TODO: remove
function removeTaskFromCalendar(action, taskID) 
{
    switch(action) 
    {
    case 'remove':
    case 'complete':
        break;
    default:
        return;

    }
    /*
    let el = document.querySelector(data-task-id="taskID");
    
    */
    
    console.log(action, taskID);
}

function addTaskToCalendar(task,month, year, uuid) 
{
    const date = new Date(task.end_date);

    let day = date.getDate();

    setIfNotExist(calendarTasks, [year, month, uuid, day], []);

    calendarTasks[year][month][uuid][day].push(task);

    DATA.AddTask(uuid, task);

    let dayEl = document.querySelector(`[data-date="${day}"]`);
    dayEl.querySelector('.tasks-count').innerHTML = ++dayEl.dataset['count'];
}

function addTasksToCalendar(tasks,month, year, uuid) 
{
    setIfNotExist(calendarTasks, [year, month, uuid], {});
    tasks.forEach(task => addTaskToCalendar(task, month, year, uuid));
}


function setIfNotExist(obj, path, value) 
{
    const lastKey = path.pop();

    const nested = path.reduce((prev, curr) => {
        if (prev[curr] === undefined)
            return prev[curr] = {};
        else 
            return prev[curr];
    }, obj);

    if (!nested.hasOwnProperty(lastKey))
        nested[lastKey] = value;

}

function isLoaded(month, year, uuid) 
{
    return calendarTasks[year] !== undefined && calendarTasks[year][month] !== undefined && calendarTasks[year][month][uuid] !== undefined;
}

Kantodo.info("Calendar loaded");