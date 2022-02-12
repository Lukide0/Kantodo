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


        $currDay = date('j');
        $currMonth = (int)date('n') - 1;
        $currYear = (int)date('Y');

        $monthName = "";


        switch($currMonth) 
        {
        case 1:
            $monthName = t('january', 'calendar');
            break;
        case 2:
            $monthName = t('february', 'calendar');
            break;
        case 3:
            $monthName = t('march', 'calendar');
            break;
        case 4:
            $monthName = t('april', 'calendar');
            break;
        case 5:
            $monthName = t('may', 'calendar');
            break;
        case 6:
            $monthName = t('june', 'calendar');
            break;
        case 7:
            $monthName = t('july', 'calendar');
            break;
        case 8:
            $monthName = t('august', 'calendar');
            break;
        case 9:
            $monthName = t('september', 'calendar');
            break;
        case 10:
            $monthName = t('october', 'calendar');
            break;
        case 11:
            $monthName = t('november', 'calendar');
            break;
        case 12:
            $monthName = t('december', 'calendar');
            break;
        default:
            break;
        }

        // TODO:
?>
        <div class="controls">
            <div class="row middle space-medium-bottom">
                <button class="icon primary outline">keyboard_arrow_left</button>
                <div class="date"><?= $monthName . " " . $currYear ?></div>
                <button class="icon primary outline">keyboard_arrow_right
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
        window.addEventListener('load', function(){
            loadMonth(<?= $currYear ?>, <?= $currMonth ?>, new Date());
            console.log(daysContainer);
        });
        </script>
<?php

    }
}
