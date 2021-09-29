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

    const STATUS_CODE_BAD_REQUEST  = 400;
    const STATUS_CODE_UNAUTHORIZED = 401;
    const STATUS_CODE_FORBIDDEN    = 403;
    const STATUS_CODE_NOT_FOUND    = 404;

    const STATUS_CODE_INTERNAL_SERVER_ERROR = 500;

    const CACHE_NON = 'no-cache';

    /**
     * Odpověď
     *
     * @var array<string,array<mixed>>
     */
    protected $responseJSON = [
        'errors' => [],
        'meta'   => [],
        'data'   => [],
    ];

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

    /**
     * Přidá meta
     *
     * @param   string  $key    klíč
     * @param   mixed  $value   hodnota
     *
     * @return  void
     */
    public function addResponseMeta(string $key, $value)
    {
        if (isset($this->responseJSON['header'][$key])) {
            if (is_array($this->responseJSON['header'][$key])) {
                $this->responseJSON['header'][$key][] = $value;
            } else {
                $tmp                                = $this->responseJSON['header'][$key];
                $this->responseJSON['header'][$key] = [$tmp, $value];
            }
        } else {
            $this->responseJSON['header'][$key] = $value;
        }

    }

    /**
     * Přidá error
     *
     * @param   string  $error    error
     *
     * @return  void
     */
    public function addResponseError(string $error)
    {
        $this->responseJSON['errors'][] = $error;
    }

    /**
     * Přidá data
     *
     * @param   mixed  $data    data
     *
     * @return  void
     */
    public function addResponseData($data)
    {
        $this->responseJSON['data'][] = $data;
    }

    /**
     * Nastaví meta
     *
     * @param   mixed  $meta    meta
     *
     * @return  void
     */
    public function setResponseMeta($meta)
    {
        $this->responseJSON['meta'] = $meta;
    }

    /**
     * Nastaví chyby
     *
     * @param   array<string>  $errors   chyby
     *
     * @return  void
     */
    public function setResponseErrors(array $errors)
    {
        $this->responseJSON['errors'] = $errors;
    }

    /**
     * Nastaví data
     *
     * @param   mixed  $data   data
     *
     * @return  void
     */
    public function setResponseData($data)
    {
        $this->responseJSON['data'] = $data;
    }

    /**
     * @return  void
     */
    public function outputResponse()
    {
        echo json_encode($this->responseJSON);
    }

    /**
     * Vyčistí odpověd
     *
     * @return  void
     */
    public function clearResponse()
    {
        $this->responseJSON = [
            'errors' => [],
            'meta'   => [],
            'data'   => [],
        ];
    }
}
