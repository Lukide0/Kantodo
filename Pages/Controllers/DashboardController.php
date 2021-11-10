<?php 

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
        $projects = $projectModel->getUserProjects(Application::$APP->session->get('user')['id']);

        if ($projects === false)
            $projects = [];

        $taskModel = new TaskModel();
        foreach ($projects as &$project) {
            $project['tasks'] = $taskModel->get(['name', 'priority', 'end_date', 'creator_id', 'milestone_id', 'completed', 'task_id'], ['project_id' => $project['project_id']]);
        }
        $this->renderView(DashboardView::class, ['projects' => $projects], ClientLayout::class);
    }
}



?>