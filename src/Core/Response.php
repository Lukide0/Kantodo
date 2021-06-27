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

    public function SetStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function SetLocation(string $location = '/') 
    {
        $url = Application::$URL_PATH . $location;

        header("location:$url");
    }

    public function SetCacheControl(string $cache = self::CACHE_NON) 
    {
        header("Cache-Control: $cache");
    }

    public function SetContentType(string $type = self::CONTENT_TYPE_JSON) 
    {
        header("Content-Type: $type");
    }

    public function AddResponseError(string $error) 
    {
        $this->responseJSON['errors'][] = $error;
    }

    public function AddResponseData(string $data) 
    {
        $this->responseJSON['data'][] = $data;
    }

    public function SetResponeMeta($meta) 
    {
        $this->responseJSON['meta'] = $meta;
    }

    public function SetResponeErrors(array $errors) 
    {
        $this->responseJSON['errors'] = $errors;
    }

    public function SetResponeData(array $data) 
    {
        $this->responseJSON['data'] = $data;
    }

    public function OutputResponse() 
    {
        echo json_encode($this->responseJSON);
    }
    
    public function ClearResponse() 
    {
        $this->responseJSON = [
            'errors' => [],
            'meta' => [],
            'data' => []
        ];
    }

    public function FlushResponse() 
    {
        $this->OutputResponse();
        ob_flush();
        flush();

        $this->ClearResponse();
    }
}


?>