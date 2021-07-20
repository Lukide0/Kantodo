<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Controller;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\ProjectsListView;

class ProjectController extends Controller
{
    public function projectsList()
    {
        $this->renderView(ProjectsListView::class, [], ClientLayout::class);
    }
}



?>