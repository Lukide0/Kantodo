<?php

namespace Kantodo\API\Controllers;

use Kantodo\API\API;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Core\Request;
use Kantodo\API\Response;
use Kantodo\Auth\Auth;
use Kantodo\Core\Validation\Data;

use function Kantodo\Core\Functions\t;

class AuthController extends AbstractController
{
    /**
     * Akce na obnovení paseto tokenu
     *
     * @return  void
     */
    public function refreshToken()
    {
        $body = API::$APP->request->getBody();
        $response = API::$APP->response;

        $keys = [
            'email',
            'secret',
        ];

        $empty = Data::empty($body[Request::METHOD_POST], $keys);

        if (count($empty) != 0) 
        {
            $response->fail(array_fill_keys($empty, t('empty', 'api')));
        }

        $token = Auth::refreshByCredentials($body[Request::METHOD_POST]['email'], $body[Request::METHOD_POST]['secret']);

        if ($token === null) 
        {
            $response->error(t('cannot_create', 'api'), Response::STATUS_CODE_INTERNAL_SERVER_ERROR);
        } else if ($token === false) 
        {
            $response->error(t('invalid_credentials'), Response::STATUS_CODE_BAD_REQUEST);
        } else {
            $response->success(['token' => $token]);
        }
    }
}
