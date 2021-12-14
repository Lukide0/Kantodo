<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Views\CalendarView;
use Kantodo\Views\Layouts\ClientLayout;

class CalendarController extends AbstractController
{
    public function default()
    {
        $this->renderView(CalendarView::class, [], ClientLayout::class);
    }
}



?>