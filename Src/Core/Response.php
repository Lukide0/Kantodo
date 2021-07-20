<?php 

namespace Kantodo\Core;

class Response
{
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_EVENT_STREAM = 'text/event-stream';


    const CACHE_NON = 'no-cache';
    protected $responseJSON = [
        'errors' => [],
        'meta' => [],
        'data' => []
    ];

    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function setLocation(string $location = '/', bool $external = false) 
    {
        if (!$external)
            $url = Application::$URL_PATH . $location;

        header("location:$url");
    }

    public function setCacheControl(string $cache = self::CACHE_NON) 
    {
        header("Cache-Control: $cache");
    }

    public function setContentType(string $type = self::CONTENT_TYPE_JSON) 
    {
        header("Content-Type: $type");
    }

    public function addResponseMeta($key, $value)
    {
        if (isset($this->responseJSON['header'][$key])) 
        {
            if (is_array($this->responseJSON['header'][$key]))
                $this->responseJSON['header'][$key][] = $value;
            else 
            {
                $tmp = $this->responseJSON['header'][$key];
                $this->responseJSON['header'][$key] = [$tmp, $value];
            }
        }
        else
            $this->responseJSON['header'][$key] = $value;
    }

    public function addResponseError(string $error) 
    {
        $this->responseJSON['errors'][] = $error;
    }

    public function addResponseData($data) 
    {
        $this->responseJSON['data'][] = $data;
    }

    public function setResponseMeta($meta) 
    {
        $this->responseJSON['meta'] = $meta;
    }

    public function setResponseErrors(array $errors) 
    {
        $this->responseJSON['errors'] = $errors;
    }

    public function setResponseData($data) 
    {
        $this->responseJSON['data'] = $data;
    }

    public function outputResponse() 
    {
        echo json_encode($this->responseJSON);
    }
    
    public function clearResponse() 
    {
        $this->responseJSON = [
            'errors' => [],
            'meta' => [],
            'data' => []
        ];
    }

    public function flushResponse() 
    {
        $this->outputResponse();
        ob_flush();
        flush();

        //$this->clearResponse();
    }
}


?>