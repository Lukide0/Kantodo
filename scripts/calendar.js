const daysContainer = document.querySelector(".days");

var calendarTasks = {};

function loadMonth(year, month, currentDate) {
    daysContainer.innerHTML = "";

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
    let tasks = calendarTasks[year][month][day] ?? [];
    
    // TODO: show tasks => copy-paste from dashboard
}

function addTaskToDay(task) 
{
    const date = new Date(task.end_date);

    let day = date.getDate();
    let month = date.getMonth();
    let year = date.getFullYear();

    if (calendarTasks[year] === undefined) 
    {
        calendarTasks[year] = {};
        calendarTasks[year][month] = {};
        calendarTasks[year][month][day] = [task];
    } 
    else if (calendarTasks[year][month] === undefined) 
    {
        calendarTasks[year][month] = {};
        calendarTasks[year][month][day] = [task];
    }
    else if (calendarTasks[year][month][day] === undefined) 
    {
        calendarTasks[year][month][day] = [task];
    }
    else 
    {
        calendarTasks[year][month][day].push(task);  
    }
    let dayEl = document.querySelector(`[data-date="${day}"]`);
    dayEl.querySelector('.tasks-count').innerHTML = ++dayEl.dataset['count'];
}

function isLoaded(month, year) 
{
    return calendarTasks[year] !== undefined && calendarTasks[year][month] !== undefined;
}

Kantodo.info("Calendar loaded");