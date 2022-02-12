const daysContainer = document.querySelector(".days");

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
        <div data-date="${i}" class="day">
            <div class="name">${i}</div>
            <div class="tasks-count">0 tasks</div>
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


Kantodo.info("Calendar loaded");