<?php

namespace Kantodo\API;

use Kantodo\Core\Response as CoreResponse;

class Response extends CoreResponse
{

    /**
     * Odpoví zprávout typu Success
     *
     * @param   array<string,mixed>|null  $data  data
     *
     * @return  void
     */
    public function success(array $data = null, int $code = self::STATUS_CODE_OK)
    {
        http_response_code($code);
        $this->setContentType(self::CONTENT_TYPE_JSON);
        echo json_encode(['status' => 'success', 'data' => $data, 'code' => $code]);
        exit;
    }

    /**
     * Odpoví zprávout typu Fail
     *
     * @param   array<string,string>  $data  data
     *
     * @return  void
     */
    public function fail(array $data, int $code = self::STATUS_CODE_BAD_REQUEST)
    {
        http_response_code($code);
        $this->setContentType(self::CONTENT_TYPE_JSON);
        echo json_encode(['status' => 'fail', 'data' => $data, 'code' => $code]);
        exit;
    }

    /**
     * Odpoví zprávout typu Error
     *
     * @param   string  $message  zpráva
     *
     * @return  void
     */
    public function error(string $message, int $code = self::STATUS_CODE_UNAUTHORIZED)
    {
        http_response_code($code);
        $this->setContentType(self::CONTENT_TYPE_JSON);
        echo json_encode(['status' => 'error', 'error' => $message, 'code' => $code]);
        exit;
    }

}
