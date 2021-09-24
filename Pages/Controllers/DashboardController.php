<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Views\DashboardView;
use Kantodo\Views\Layouts\ClientLayout;

class DashboardController extends AbstractController
{
    public function dashboard()
    {
        $this->renderView(DashboardView::class, [], ClientLayout::class);
    }
}



?>