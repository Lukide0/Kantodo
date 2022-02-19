<?php

declare (strict_types = 1);

namespace Kantodo\API\Controllers;

use DateTime;
use function Kantodo\API\connectToWebsoketServer;
use function Kantodo\Core\Functions\base64DecodeUrl;
use function Kantodo\Core\Functions\t;
use Kantodo\API\API;
use Kantodo\Auth\Auth;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Core\Validation\Data;
use Kantodo\Core\Validation\DataType;
use Kantodo\Models\ProjectModel;
use Kantodo\Models\TagModel;
use Kantodo\Models\TaskModel;

class TaskController extends AbstractController
{
    /**
     * Akce na vytvoření úkolu
     *
     * @return  void
     */
    public function create()
    {
        $body     = API::$API->request->getBody();
        $response = API::$API->response;

        $keys = [
            'task_name',
            'task_desc',
            'task_proj',
            'task_comp',
            'task_priority',
        ];

        $empty = Data::notSet($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        $taskName      = $body[Request::METHOD_POST]['task_name'];
        $taskDesc      = $body[Request::METHOD_POST]['task_desc'];
        $taskCompleted = $body[Request::METHOD_POST]['task_comp'];
        $taskPriority  = $body[Request::METHOD_POST]['task_priority'];
        $taskEndDate   = $body[Request::METHOD_POST]['task_end_date'] ?? null;

        $projRAW  = $body[Request::METHOD_POST]['task_proj'];
        $projUUID = base64DecodeUrl($projRAW);
        $user     = Auth::getUser();

        if ($projUUID === false) {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if ($user === null || empty($user['id'])) {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        if (!DataType::wholeNumber($taskCompleted, 0, 1) || !DataType::wholeNumber($taskPriority, 0, 2)) {
            $response->fail(['error' => t('bad_request')]);
            exit;
        }

        $taskPriority  = (int) $taskPriority;
        $taskCompleted = (bool) $taskCompleted;

        if ($taskEndDate !== null) {
            $date = new DateTime($taskEndDate);

            if ($date != false) {
                $taskEndDate = $date;
            } else {
                $response->fail(['error' => t('bad_request')]);
                exit;
            }
        }

        $projModel = new ProjectModel();

        $details = $projModel->getBaseDetails((int) $user['id'], $projUUID);
        if ($details === false) {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        $priv = $projModel->getPositionPriv($details['name']);

        if ($priv === false || !$priv['addTask']) {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $taskModel = new TaskModel();

        $taskID = $taskModel->create($taskName, (int) $user['id'], (int) $details['id'], $taskDesc, $taskPriority, $taskEndDate, $taskCompleted);
        if ($taskID === false) {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }

        if (!empty($body[Request::METHOD_POST]['task_tags']) && is_array($body[Request::METHOD_POST]['task_tags'])) {
            $tagModel = new TagModel();

            $tags = [];
            foreach ($body[Request::METHOD_POST]['task_tags'] as $tag) {
                $tagID = $tagModel->createInProject($tag, (int) $details['id']);

                if ($tagID === false) {
                    $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
                } else {
                    $tags[] = $tagID;
                }

            }

            $status = $tagModel->addTagsToTask($tags, $taskID);

            if ($status === false) {
                $response->error(t('can_not_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            }

        }

        // https://stackoverflow.com/a/28738208
        ob_start();

        $response->success(
            [
                'task' =>
                [
                    'id' => $taskID,
                ],
            ],
            Response::STATUS_CODE_CREATED
        );

        $size = ob_get_length();

        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");

        ob_end_flush();
        @ob_flush();
        flush();

        connectToWebsoketServer(
            Auth::$PASETO_RAW, 
            'task_create',
            [
                'id'          => $taskID,
                'name'        => $taskName,
                'description' => $taskDesc,
                'priority'    => $taskPriority,
                'completed'   => $taskCompleted,
                'end_date'    => $taskEndDate,
            ],
            $projRAW
        );
    }

    /**
     * Akce na získání úkolů v projektu
     *
     * @param   array<string>  $params  parametry
     *
     * @return  void
     */
    public function get(array $params = [])
    {
        $limit    = 10;
        $response = API::$API->response;
        $user     = Auth::getUser();
        $body     = API::$API->request->getBody();

        $last  = ($body[Request::METHOD_GET]['last'] ?? 0);
        $month = ($body[Request::METHOD_GET]['month'] ?? null);
        $year  = ($body[Request::METHOD_GET]['year'] ?? null);

        if (DataType::wholeNumber($last)) {
            $last = (int) $last;
        } else {
            $last = 0;
        }

        if (DataType::wholeNumber($month, 1, 12) && DataType::wholeNumber($year, 1)) {
            $month = (int) $month;
            $year  = (int) $year;
        } else {
            $month = null;
            $year  = null;
        }

        if ($user === null || empty($user['id'])) {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        if (empty($params['projectUUID'])) {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
        }

        $uuid = base64DecodeUrl($params['projectUUID']);

        if ($uuid === false) {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $projectId = 0;
        if (ProjectModel::hasPrivTo('viewTask', (int) $user['id'], $uuid, $projectId) === false) {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $search = [
            'project_id' => $projectId,
            'task_id'    => ['>', $last],
        ];

        if (!is_null($month)) {
            $search['CUSTOM_WHERE'] = [
                "MONTH(end_date) = :month AND YEAR(end_date) = :year AND end_date IS NOT NULL AND completed = 0",
                [
                    ":month" => $month,
                    ":year"  => $year,
                ],
            ];
            // nacte prvnich 500
            $limit = 500;
        }

        $taskModel = new TaskModel();
        $tasks     = $taskModel->get(
            ['task_id' => 'id', 'name', 'description', 'priority', 'completed', 'end_date'],
            $search,
            $limit
        );

        if ($tasks === false) {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }

        $tagModel = new TagModel();

        foreach ($tasks as &$task) {
            $taskID       = (int) $task['id'];
            $task['tags'] = $tagModel->getTaskTags($taskID);
        }

        $response->success(['tasks' => $tasks]);
    }

    /**
     * Akce na úpravu úkolu
     *
     * @return  void
     */
    public function update()
    {
        $body     = API::$API->request->getBody();
        $response = API::$API->response;
        $user     = Auth::getUser();

        $keys = [
            'task_id',
            'task_proj',
        ];

        $empty = Data::notSet($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        if ($user === null || empty($user['id'])) {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        $taskID   = $body[Request::METHOD_POST]['task_id'];
        $taskProj = $body[Request::METHOD_POST]['task_proj'];

        $uuid = base64DecodeUrl($taskProj);

        if ($uuid === false) {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if (!DataType::wholeNumber($taskID, 1)) {
            $response->error(t('task_id_is_not_valid', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $tags     = $body[Request::METHOD_POST]['task_tags'] ?? [];
        $taskData = [
            'name'        => $body[Request::METHOD_POST]['task_name'] ?? null,
            'priority'    => $body[Request::METHOD_POST]['task_priority'] ?? null,
            'completed'   => $body[Request::METHOD_POST]['task_comp'] ?? null,
            'description' => $body[Request::METHOD_POST]['task_desc'] ?? null,
            'end_date'    => strtotime($body[Request::METHOD_POST]['task_end_date'] ?? ""),
        ];

        foreach ($taskData as $key => $value) {
            if ($value === null) {
                unset($taskData[$key]);
            }
        }

        if (count($taskData) == 0) {
            $response->fail([]);
            exit;
        }

        if (isset($taskData['end_date'])) {
            if ($taskData['end_date'] == false) {
                unset($taskData['end_date']);
            } else {
                $taskData['end_date'] = date(Connection::DATABASE_DATE_FORMAT, $taskData['end_date']);
            }
        }

        if (isset($taskData['name']) && strlen($taskData['name']) == 0) {
            $response->fail(['error' => t('bad_request')]);
            exit;
        }

        if (isset($taskData['completed']) && !DataType::wholeNumber($taskData['completed'], 0, 1)) {
            $response->fail(['error' => t('bad_request')]);
            exit;
        }

        if (isset($taskData['priority']) && !DataType::wholeNumber($taskData['priority'], 0, 2)) {
            $response->fail(['error' => t('bad_request')]);
            exit;
        }

        $projectId = 0;
        if (ProjectModel::hasPrivTo('editTask', (int) $user['id'], $uuid, $projectId) !== true) {

            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $taskModel = new TaskModel();

        $exists = $taskModel->getSingle(['task_id'], ['project_id' => $projectId, 'task_id' => $taskID]);

        if ($exists === false) {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $status = $taskModel->update((int) $taskID, $taskData);

        if ($status === false) {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }

        $tagModal = new TagModel();
        $status   = $tagModal->setTagsToTask($tags, (int) $taskID, $projectId);

        if ($status === false) {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }

        // https://stackoverflow.com/a/28738208
        ob_start();

        $response->success(null, Response::STATUS_CODE_OK);

        $size = ob_get_length();

        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");

        ob_end_flush();
        @ob_flush();
        flush();

        connectToWebsoketServer(
            Auth::$PASETO_RAW,
            'task_update',
            [
                'id'      => $taskID,
                'changed' => $taskData,
            ],
            $taskProj
        );
    }

    /**
     * Akce na odstranění úkolu z projektu
     *
     * @return  void
     */
    public function remove()
    {
        $body     = API::$API->request->getBody();
        $response = API::$API->response;
        $keys     = [
            'task_id',
            'task_proj',
        ];

        $empty = Data::notSet($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
        }

        $taskID   = $body[Request::METHOD_POST]['task_id'];
        $projRAW  = $body[Request::METHOD_POST]['task_proj'];
        $projUUID = base64DecodeUrl($projRAW);
        $user     = Auth::getUser();

        if ($projUUID === false) {
            $response->error(t('project_uuid_missing', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if (!DataType::wholeNumber($taskID, 1)) {
            $response->error(t('task_id_is_not_valid', 'api'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        if ($user === null || empty($user['id'])) {
            $response->error(t('user_id_missing', 'api'));
            exit;
        }

        $projModel = new ProjectModel();
        $details   = $projModel->getBaseDetails((int) $user['id'], $projUUID);
        if ($details === false) {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }
        $priv = $projModel->getPositionPriv($details['name']);
        if ($priv === false || !$priv['removeTask']) {
            $response->error(t('you_dont_have_sufficient_privileges', 'api'), Response::STATUS_CODE_FORBIDDEN);
            exit;
        }

        $taskModel = new TaskModel();
        $status    = $taskModel->delete((int) $taskID);
        if ($status === false) {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }

        // https://stackoverflow.com/a/28738208
        ob_start();

        $response->success(null, Response::STATUS_CODE_OK);

        $size = ob_get_length();

        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");

        ob_end_flush();
        @ob_flush();
        flush();

        connectToWebsoketServer(Auth::$PASETO_RAW, 'task_remove', ['id' => $taskID], $projRAW);
    }
}
