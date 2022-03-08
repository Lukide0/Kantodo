<?php

declare(strict_types=1);

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\Core\Response;
use Kantodo\Auth\Auth;
use Kantodo\Core\Validation\Data;
use Kantodo\Models\UserModel;

use function Kantodo\Core\Functions\t;

class AuthController extends AbstractController
{
    /**
     * Akce na obnovení paseto tokenu
     * Není implementována na front-end
     *
     * @return  void
     */
    public function refreshToken()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $keys = [
            'email',
            'secret',
        ];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        $token = Auth::refreshByCredentials($body[Request::METHOD_POST]['email'], $body[Request::METHOD_POST]['secret']);

        if ($token === null) {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        } else if ($token === false) {
            $response->error(t('invalid_credentials'), Response::STATUS_CODE_BAD_REQUEST);
        } else {
            $response->success(['token' => $token]);
        }
    }

    /**
     * Akce na smazání účtu
     * 
     * @return  void
     */
    public function removeAccount()
    {
        $body = API::$API->request->getBody();
        $response = API::$API->response;
        $keys = [
            'email',
            'password'
        ];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
            exit;
        }

        $email = $body[Request::METHOD_POST]['email'];
        $password = Auth::hashPassword($body[Request::METHOD_POST]['password'], $email);

        $user = Auth::getUser();

        if ($user == null || $user['email'] != $email) {
            $response->error(t('invalid_credentials'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }

        $userModel = new UserModel();
        $exists = $userModel->exists(['email' => $email, 'password' => $password]);

        if (!$exists) {
            $response->error(t('invalid_credentials'), Response::STATUS_CODE_BAD_REQUEST);
            exit;
        }


        $status = $userModel->delete((int)$user['id']);
        if ($status) {
            Auth::signOut();
            $response->success([]);
        } else {
            $response->error(t('something_went_wrong', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }
}
