<?php 

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Models\ProjectModel;
use Kantodo\Views\DashboardView;
use Kantodo\Views\Layouts\ClientLayout;

class DashboardController extends AbstractController
{
    /**
     * Homapage
     *
     * @return  void
     */
    public function view()
    {
        $projectModel = new ProjectModel();

        $projects = $projectModel->getUserProjects(Application::$APP->session->get('user')['id']);

        $this->renderView(DashboardView::class, ['projects' => $projects], ClientLayout::class);
    }
}



?>