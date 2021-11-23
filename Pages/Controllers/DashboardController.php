<?php

declare(strict_types = 1);

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TaskModel;
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
        $projects = $projectModel->getUserProjects((int)Application::$APP->session->get('user')['id']);

        if ($projects === false)
            $projects = [];
        $this->renderView(DashboardView::class, ['projects' => $projects], ClientLayout::class);
    }
}



?>