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

    public function AddResponseMeta($key, $value)
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

    public function AddResponseError(string $error) 
    {
        $this->responseJSON['errors'][] = $error;
    }

    public function AddResponseData($data) 
    {
        $this->responseJSON['data'][] = $data;
    }

    public function SetResponseMeta($meta) 
    {
        $this->responseJSON['meta'] = $meta;
    }

    public function SetResponseErrors(array $errors) 
    {
        $this->responseJSON['errors'] = $errors;
    }

    public function SetResponseData($data) 
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

        //$this->ClearResponse();
    }
}


?>