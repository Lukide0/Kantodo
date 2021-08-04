<?php 

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\IView;

class HomeView implements IView
{
    public function Render(array $params = [])
    {
        $hour = date('H');
        
        Application::$APP->header->registerScript('/scripts/calendar.js', false);

        $session = Application::$APP->session;

        $msg = '';
        $name = $session->get('user')['firstname'];
        $date = date("l j. F Y");

        if ($hour < 12) 
            $msg = 'morning';
        else if ($hour < 17)
            $msg = 'afternoon';
        else if ($hour < 21)
            $msg = 'evening';
        else
            $msg = 'night';
        
        ?>
        <h3 class="font-weight-black">Good <?= $msg ?>, <?= $name ?>!</h3>
        <p class="font-size-regular font-weight-bold"><?= $date ?></p>
        <div id="calendarContainer" class="container main-center cross-center font-size-huge font-weight-black" style="background-color: aqua;">
            CALENDAR
        </div>
        <script>
            const calendar = Calendar();

        </script>

        <?php
    }
}

?>