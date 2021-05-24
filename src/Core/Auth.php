<?php

namespace Kantodo\Core;

class Auth
{
    private function __construct() {}

    public static function IsSignIn()
    {

        if (empty($_SESSION) OR
            empty($_SESSION['userID']) OR
            empty($_SESSION['exp']))
        {
            return false;
        }

        if ($_SESSION['exp'] <= time()) return false;

        // DB user EXISTS
        return true;

    }

    public static function SignIn(string $firstName,string $lastName,string $email,string $password) 
    {
    }
    public static function SignOut()
    {
        if (empty($_SESSION)) return;
        session_unset();
    }

    public static function GetToken() 
    {
        if (!Auth::IsSignIn()) return '';

        // DB CALL
    }

    public static function GenerateToken() 
    {

    }

    public static function ValidateToken(string $token) 
    {
        return true;
    }
}
    


?>