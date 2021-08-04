<?php

namespace Kantodo\Core;

use Kantodo\Core\Validation\Data;
use Kantodo\Models\UserModel;

class Auth
{
    const EXP = 60 * 30;

    private function __construct() {}

    public static function isLogged()
    {

        $session = Application::$APP->session;

        if ($session->getExpiration('user') <= time())
            return false;
        
        $userModel = new UserModel();
        

        $search = [
            'user_id' => $session->get('user')['id'],
            'email' => $session->get('user')['email'],
            'secret' => $session->get('user')['secret']
        ];

        if ($userModel->exists($search) === false)
        {
            $session->cleanData();
            return false;
        }
        $session->setExpiration('user', time() + self::EXP);
        return true;
        
    }
    
    public static function signIn(string $email,string $password) 
    {
        $userModel = new UserModel();
        
        $user = $userModel->getSingle(['user_id' => 'id', 'secret', 'firstname', 'lastname'],[
            'email' => $email,
            'password' => Data::hashPassword($password, $email)
        ]);

        
        $session = Application::$APP->session;

        if ($user !== false) 
        {
            $user['email'] = $email;
            $user['role'] = Application::USER;

            $session->set("user", $user, time() + self::EXP);
            
            return true;
        }

        return false;
    }

    public static function signOut()
    {
        Application::$APP->session->cleanData();
    }

}
    


?>