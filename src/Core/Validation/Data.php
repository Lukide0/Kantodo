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

    public static function IsValidPassword(string $password ,bool $mustContainNumber = false, bool $mustContainSpecialChar = false, bool $mustContainUppercaseChar = false) 
    {
        if (strlen($password) == 0 )
            return false;

        if ($mustContainNumber && !preg_match('[0-9]', $password))
            return false;

        if ($mustContainSpecialChar && !preg_match('[\W]', $password))
            return false;
        
        if ($mustContainUppercaseChar && !preg_match('[A-Z]', $password))
            return false;
        
        return true;

    }

    public static function HashPassword(string $password, string $salt = '') 
    {
        $middle = floor( strlen($salt) / 2 );

        $password = substr($salt, 0, $middle) . $password . substr($salt, $middle);

        return hash('sha256', $password);
    }

    public static function FormatName(string $name) 
    {
        // remove space, tab
        $name = trim($name);
        
        // name contains only spaces or tabs
        if (strlen($name) == 0)
            return false;
        
        // name contains space
        if (strpos($name, ' '))
            return false;

        // first char uppercase
        $name = ucfirst( strtolower($name) );
        return $name;
    }

}



?>