<?php 

namespace Kantodo\Core\Validation;

use Kantodo\Core\Application;

class Data
{
    private function __construct() { }

    public static function empty(array $data, array $keys) 
    {
        $emptyKeys = [];
        foreach ($keys as $key) {
            if (!isset($data[$key]) || empty($data[$key])) 
            {
                $emptyKeys[] = $key;
            }
        }

        return $emptyKeys;

    }

    public static function isEmpty(array $data, array $keys) 
    {
        foreach ($keys as $key) {
            if (empty($data[$key]))
                return true;
        }

        return false;


    }
    public static function notSet(array $data, array $keys)
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

    public static function setIfNotSet(array &$data, array $keys, $value) 
    {
        foreach ($keys as $key) {
            if (!isset($data[$key])) 
            {
                $data[$key] = $value;
            }
        }
    }

    public static function fillEmpty(array &$data, array $keys, $value) 
    {
        foreach ($keys as $key) {
            if (empty($data[$key])) 
            {
                $data[$key] = $value;
            }
        }
    }

    public static function isValidPassword(string $password ,bool $mustContainNumber = false, bool $mustContainSpecialChar = false, bool $mustContainUppercaseChar = false) 
    {
        if (strlen($password) == 0 )
            return false;

        if ($mustContainNumber && !preg_match('/[0-9]/', $password))
            return false;

        if ($mustContainSpecialChar && !preg_match('/[`!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?~]/', $password))
            return false;
        
        if ($mustContainUppercaseChar && !preg_match('/[A-Z]/', $password))
            return false;
        return true;

    }

    public static function isValidEmail(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function hashPassword(string $password, string $salt = '') 
    {
        $middle = floor( strlen($salt) / 2 );

        $password = substr($salt, 0, $middle) . $password . substr($salt, $middle);

        return hash('sha256', $password);
    }

    public static function formatName(string $name) 
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

    public static function isURLExternal(string $url)
    {
        $link = parse_url($url);
        $home = parse_url($_SERVER['HTTP_HOST']);
        if (empty($link['host'])) 
            return false;

        if ($link['host'] == $home['host'])
            return false;

        return true;
    }

}



?>