<?php

declare(strict_types=1);

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Middlewares\ProjectMiddleware;
use Kantodo\Views\Layouts\ClientLayout;
use Kantodo\Views\ProjectSettingsView;
use Kantodo\Views\ProjectView;

/**
 * Třída na práci s projektem
 */
class ProjectController extends AbstractController
{

    public function __construct()
    {
        $this->registerMiddleware(new ProjectMiddleware);
    }

    /**
     * Zobrazení projetu
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    public function view(array $params = [])
    {
        $this->renderView(ProjectView::class, $params, ClientLayout::class);
    }

    /**
     * Zobrazení nastavení projektu
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  void
     */
    public function settings(array $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (!$params['priv']['changePeoplePosition'] && !$params['priv']['removePeople']) {
            Application::$APP->response->setLocation("/project/{$params['projectUUID']}");
            exit;
        }

        $this->renderView(ProjectSettingsView::class, $params, ClientLayout::class);
    }
}
