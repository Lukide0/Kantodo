<?php

namespace Kantodo\Controllers;

use function Kantodo\Core\Functions\base64DecodeUrl;
use Kantodo\Core\Application;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Response;
use Kantodo\Core\Validation\Data;
use Kantodo\Core\Validation\DataType;
use Kantodo\Models\ColumnModel;
use Kantodo\Models\ProjectModel;

/**
 * Třída na práci se sloupci v projektu
 */
class ColumnController extends AbstractController
{
    /**
     * Akce na vytvoření sloupce
     *
     * @param   array  $params  parametry z url
     *
     * @return  void
     */
    public function createColumn(array $params = [])
    {

        $response = Application::$APP->response;

        $body = Application::$APP->request->getBody();

        $projID = $params['projID'] ?? false;

        if ($projID === false) {
            $response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        // název sloupce
        if (Data::isEmpty($body['post'], ['columnName'])) {
            $response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        // IDs
        $projID = base64DecodeUrl($projID);
        $userID = Application::$APP->session->get('user')['id'];

        $projModel = new ProjectModel();

        // pozice uživatele v projektu
        $userProjPos = $projModel->getUserProjectPosition($projID, $userID);

        if ($userProjPos === false) {
            $response->setStatusCode(Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        // oprávnění uživatele
        $userProjPosPriv = $projModel->getPositionPriv($userProjPos);

        if ($userProjPosPriv['addColumn'] === false) {
            $response->setStatusCode(Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $columnModel = new ColumnModel();

        $maxTasksCount = null;
        $columnName    = $body['post']['columnName'];

        // maximální počet úkolů ve sloupci
        if (isset($body['post']['maxTasksCount']) && DataType::wholeNumber($body['post']['maxTasksCount'], 1)) {
            $maxTasksCount = $body['post']['maxTasksCount'];
        }

        $columnID = $columnModel->create($columnName, $projID, $maxTasksCount);

        // nepodařilo uložit do databáze
        if ($columnID === false) {
            $response->setStatusCode(Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }

        $response->setStatusCode(Response::STATUS_CODE_CREATED);
    }
}
