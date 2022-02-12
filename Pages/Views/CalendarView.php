<?php

declare(strict_types = 1);

namespace Kantodo\Views;

use DateTime;
use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

use function Kantodo\Core\Functions\t;

/**
 * Kalendář
 */
class CalendarView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->setTitle("Kantodo - Calendar");
        Application::$APP->header->registerStyle("/styles/calendar.min.css");
        Application::$APP->header->registerScript("/scripts/calendar.js", false, true);

        $currMonth = (int)date('n') - 1;
        $currYear = (int)date('Y');

        $monthName = "";


        switch($currMonth) 
        {
        case 0:
            $monthName = t('january', 'calendar');
            break;
        case 1:
            $monthName = t('february', 'calendar');
            break;
        case 2:
            $monthName = t('march', 'calendar');
            break;
        case 3:
            $monthName = t('april', 'calendar');
            break;
        case 4:
            $monthName = t('may', 'calendar');
            break;
        case 5:
            $monthName = t('june', 'calendar');
            break;
        case 6:
            $monthName = t('july', 'calendar');
            break;
        case 7:
            $monthName = t('august', 'calendar');
            break;
        case 8:
            $monthName = t('september', 'calendar');
            break;
        case 9:
            $monthName = t('october', 'calendar');
            break;
        case 10:
            $monthName = t('november', 'calendar');
            break;
        case 11:
            $monthName = t('december', 'calendar');
            break;
        default:
            break;
        }
?>
        <div class="controls">
            <div class="row middle space-medium-bottom">
                <button class="icon primary outline" onclick="previousMonth()">keyboard_arrow_left</button>
                <div class="date"><?= $monthName . " " . $currYear ?></div>
                <button class="icon primary outline" onclick="newMonth()">keyboard_arrow_right
                </button>
            </div>
            <div class="week">
                <div class="day-name"><?= t('monday_short', 'calendar') ?></div>
                <div class="day-name"><?= t('tuesday_short', 'calendar') ?></div>
                <div class="day-name"><?= t('wednesday_short', 'calendar') ?></div>
                <div class="day-name"><?= t('thursday_short', 'calendar') ?></div>
                <div class="day-name"><?= t('friday_short', 'calendar') ?></div>
                <div class="day-name"><?= t('saturday_short', 'calendar') ?></div>
                <div class="day-name"><?= t('sunday_short', 'calendar') ?></div>
            </div>
        </div>
        <div class="days">
            <!-- Dny v kalendari  -->
        </div>
        <script>
        const translationCalendar = {
            <?php
                foreach (Application::$APP->lang->getAll('calendar') as $key => $value) {
                    echo "'%{$key}%': \"$value\",";
                }
            ?>
        };

        var lastID = -1;
        const today = new Date();
        
        let calendarDate = document.querySelector('.controls .date');
        let year = today.getFullYear();
        let month = today.getMonth();

        window.addEventListener('load', loadCalendar);

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

        function newMonth() 
        {
            if (month == 11)
            {
                month = 0;
                year++;
            }
            calendarDate.innerHTML = `${mapMonth()} ${year}`;
            loadCalendar();
        }

        function previousMonth() 
        {
            if (month == 11)
            {
                month = 0;
                year++;
            }
            calendarDate.innerHTML = `${mapMonth()} ${year}`;
            loadCalendar();
        }


        function loadCalendar() 
        {
            loadMonth(year, month, today);
            // TODO: isLoaded
            console.log(isLoaded(year, month));
            if (isLoaded(year, month)) 
                return;

            Object.keys(DATA.Projects).forEach(uuid => {
                Request.Action(`/api/get/task/${uuid}?last=${lastID}&month=${month + 1}&year=${year}`, 'GET').then(project => {
                    project.data.tasks.forEach(task => {
                        addTaskToDay(task, uuid);
                    });
                });
            });
        }
        </script>
<?php

    }
}
