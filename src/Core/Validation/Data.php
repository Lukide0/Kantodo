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

    public static function NotSet(array $data, array $keys)
    {
        $notSetKeys = [];
        foreach ($keys as $key) {
            if (!isset($data[$key])) 
            {
                $notSetKeys[] = $key;
            }
        }
        return $notSetKeys;
    }

    public static function SetIfNotSet(array &$data, array $keys, $value) 
    {
        foreach ($keys as $key) {
            if (!isset($data[$key])) 
            {
                $data[$key] = $value;
            }
        }
    }

}



?>