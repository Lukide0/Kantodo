<?php 

namespace Kantodo\Controllers;

use Kantodo\Core\Controller;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\HomeView;
use Kantodo\Views\Layouts\AuthLayout;
use Kantodo\Core\Auth;



class HomeController extends Controller
{
    public function Handle()
    {
        /*if (!Auth::IsSignIn())
        {
            $this->RenderLayout(AuthLayout::class);
            exit;
        }*/

        $array[] = $_GET;
        $this->RenderView(HomeView::class, $array, ClientLayout::class);
    }
}



?>