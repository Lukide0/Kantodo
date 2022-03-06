<?php

declare(strict_types=1);

namespace Kantodo\Controllers;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Models\ProjectModel;
use Kantodo\Views\DashboardView;
use Kantodo\Views\Layouts\ClientLayout;

class DashboardController extends AbstractController
{
    /**
     * Homepage
     *
     * @return  void
     */
    public function view()
    {
        $projectModel = new ProjectModel();
        $user = Auth::getUser();

        if ($user === null) {
            Auth::signOut();
            Application::$APP->response->setLocation('/auth');
            exit;
        }

        $userID = $user['id'];

        $projects = $projectModel->getUserProjects((int)$userID);

        if ($projects === false)
            $projects = [];

        $this->renderView(DashboardView::class, ['projects' => $projects], ClientLayout::class);
    }
}
