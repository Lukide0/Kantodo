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
        $currMonth = (int)date('n');
        $currYear = date('Y');

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
            <!--<div class="day previus">
                <div class="name">28</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day previus">
                <div class="name">29</div>
                <div class="tasks-count">8 tasks</div>
            </div>
            <div class="day previus">
                <div class="name">30</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day previus">
                <div class="name">31</div>
                <div class="tasks-count">5 tasks</div>
            </div>
            <div class="day">
                <div class="name">1</div>
                <div class="tasks-count">3 tasks</div>
            </div>
            <div class="day">
                <div class="name">2</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">3</div>
                <div class="tasks-count">6 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">4</div>
                <div class="tasks-count">2 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">5</div>
                <div class="tasks-count">5 tasks</div>
            </div>
            <div class="day">
                <div class="name">6</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">7</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">8</div>
                <div class="tasks-count">6 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">9</div>
                <div class="tasks-count">7 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">10</div>
                <div class="tasks-count">3 tasks</div>
            </div>
            <div class="day">
                <div class="name">11</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">12</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">13</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day not-empty today">
                <div class="name">14</div>
                <div class="tasks-count">5 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">15</div>
                <div class="tasks-count">4 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">16</div>
                <div class="tasks-count">9 tasks</div>
            </div>
            <div class="day">
                <div class="name">17</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day not-empty">
                <div class="name">18</div>
                <div class="tasks-count">8 tasks</div>
            </div>
            <div class="day">
                <div class="name">19</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">20</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">21</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">22</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">23</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">24</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">25</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">26</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">27</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">28</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">29</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">30</div>
                <div class="tasks-count">0 tasks</div>
            </div>
            <div class="day">
                <div class="name">31</div>
                <div class="tasks-count">0 tasks</div>
            </div>-->
        </div>

<?php

    }
}
