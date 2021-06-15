<?php 

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Controller;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\InstallView;
use Kantodo\Views\HomeView;
use Kantodo\Core\Auth;



class FrontController extends Controller
{
    public function Install()
    {
        $this->RenderView(InstallView::class);
    }
}



?>