<?php

namespace Kantodo\Core;

/**
 * Odpověď
 */
class Response
{

    const CONTENT_TYPE_JSON         = 'application/json';
    const CONTENT_TYPE_EVENT_STREAM = 'text/event-stream';

    const STATUS_CODE_OK      = 200;
    const STATUS_CODE_CREATED = 201;

    const STATUS_CODE_BAD_REQUEST       = 400;
    const STATUS_CODE_UNAUTHORIZED      = 401;
    const STATUS_CODE_FORBIDDEN         = 403;
    const STATUS_CODE_NOT_FOUND         = 404;
    const STATUS_CODE_TOO_MANY_REQUESTS = 429;

    const STATUS_CODE_INTERNAL_SERVER_ERROR = 500;

    const CACHE_NON = 'no-cache';

    /**
     * Nastaví status kód
     *
     * @param   int  $code  kód
     *
     * @return  void
     */
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    /**
     * Nastaví lokalitu
     *
     * @param   string  $location  lokalita
     *
     * @return  void
     */
    public function setLocation(string $location = '/')
    {
        $url = Application::$URL_PATH . $location;

        header("location:$url");
    }

    /**
     * Nastaví Cache control
     *
     * @param   string     $cache  cache
     *
     * @return  void
     */
    public function setCacheControl(string $cache = self::CACHE_NON)
    {
        header("Cache-Control: $cache");
    }

    /**
     * Nastaví kontent typ
     *
     * @param   string             $type  typ
     *
     * @return  void
     */
    public function setContentType(string $type = self::CONTENT_TYPE_JSON)
    {
        header("Content-Type: $type");
    }
}
