<?php

namespace Kantodo\Controllers;

use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;

/**
 * Třída na práci s úkoly
 */
class TaskController extends AbstractController
{
    /**
     * Akce na vytvoření úkoli
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function createTask(array $params = [])
    {
        $body = Application::$APP->request->getBody();
        var_dump($body);
    }
}
