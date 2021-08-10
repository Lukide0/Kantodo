<?php

namespace Kantodo\Controllers;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Views\HomeView;
use Kantodo\Views\Layouts\ClientLayout;

/**
 * Stránky s kalendářem
 */
class CalendarController extends AbstractController
{

    /**
     * Hlavní stránka
     *
     * @return  void
     */
    public function homePage()
    {
        $this->renderView(HomeView::class, [], ClientLayout::class);
    }
}
