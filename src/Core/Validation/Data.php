<?php 

namespace Kantodo\Core\Validation;


class Data
{
    private function __construct() { }

    public static function Empty(array $data, array $keys) 
    {
        $emptyKeys = [];
        foreach ($keys as $key) {
            if (!isset($data[$key]) OR empty($data[$key])) 
            {
                $emptyKeys[] = $key;
            }
        }

        return $emptyKeys;

    }

}



?>