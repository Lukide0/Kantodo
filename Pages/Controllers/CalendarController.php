<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Controller;
use Kantodo\Views\HomeView;
use Kantodo\Views\Layouts\ClientLayout;

class CalendarController extends Controller
{
    public function today()
    {
        $this->renderView(HomeView::class, [], ClientLayout::class);
    }
}



?>