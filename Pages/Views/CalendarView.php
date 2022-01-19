<?php

declare(strict_types = 1);

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

/**
 * Kalendář
 */
class CalendarView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->setTitle("Kantodo - Calendar");
    }
}
