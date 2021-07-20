<?php 

namespace Kantodo\Websocket;

class Token
{
    private function __construct() {}
    
    public static function generate(string $data, string $publicKey, string $privateKey)
    {
        $signature = hash_hmac("sha256", $data . $publicKey, $privateKey, true);
        return base64_encode($signature);
    }

    public static function compare(string $knownToken, string $token) 
    {
        return hash_equals($knownToken, $token);
    }
}




?>