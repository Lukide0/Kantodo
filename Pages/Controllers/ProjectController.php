<?php

declare(strict_types = 1);

namespace Kantodo\Controllers;

use function Kantodo\Core\Functions\base64DecodeUrl;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Validation\Data;
use Kantodo\Middlewares\ProjectAccessMiddleware;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TagModel;
use Kantodo\Models\TaskModel;
use Kantodo\Models\TeamModel;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\ProjectsListView;
use Kantodo\Views\ProjectView;

/**
 * Třída na práci s projektem
 */
class ProjectController extends AbstractController
{

    /**
     * Zobrazení projetu
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    public function view(array $params = [])
    {
        $projModel = new ProjectModel();

        $projUUID = base64DecodeUrl($params['projectUUID']);

        $project = $projModel->getSingle(['*'], ['uuid' => $projUUID]);

        //$this->renderView(ProjectView::class, $params, ClientLayout::class);
    }
}
