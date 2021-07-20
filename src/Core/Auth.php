<?php

namespace Kantodo\Core;

use Kantodo\Core\Validation\Data;
use Kantodo\Models\UserModel;

class Auth
{
    private function __construct() {}

    public static function isLogged()
    {

        $session = Application::$APP->session;
        
        if ($session->get("userID", false) === false)
            return false;
        
        if ($session->get("userSecret", false) === false)
            return false;
        
        $date = date('Y-m-d H:i:s');

        if ($session->get("exp", $date) <= $date)
            return false;
        
        $userModel = new UserModel();
        
        $search = [
            'user_id' => $session->get("userID"),
            'email' => $session->get("userEmail"),
            'secret' => $session->get("userSecret")
        ];

        if ($userModel->exists($search) === false)
        {
            $session->cleanData();
            return false;
        }
        $session->set("exp", date('Y-m-d H:i:s', strtotime("+10 minutes")));
        return true;
        
    }
    
    public static function signIn(string $email,string $password) 
    {
        $userModel = new UserModel();
        
        $user = $userModel->getSingle(['user_id', 'secret'],[
            "email" => $email,
            "password" => Data::hashPassword($password, $email)
        ]);
        
        $session = Application::$APP->session;

        if ($user !== false) 
        {
           $session->set("userID", $user['user_id']);
           $session->set("userSecret", $user['secret']);
           $session->set("userEmail", $email);
           $session->set("role", Application::USER);
           $session->set("exp", date('Y-m-d H:i:s', strtotime("+10 minutes")));
            
            return true;
        }

        return false;
    }

    public static function signOut()
    {
        Application::$APP->session->cleanData();
    }

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