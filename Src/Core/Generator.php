<?php

namespace Kantodo\Core;


class Generator
{
    private function __construct() { }

    public static function uuidV4()
    {
        $uuid = random_bytes(16);
        
        // version 4
        $uuid[6] = $uuid[6] & "\x0F" | "\x4F";
        
        
        // set bits 6-7 to 10
        $uuid[8] = $uuid[8] & "\x3F" | "\x80";
        $uuid = bin2hex($uuid);
        
        //36 character UUID
        return substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4).'-'.substr($uuid, 16, 4).'-'.substr($uuid, 20, 12);
    }
}



?>