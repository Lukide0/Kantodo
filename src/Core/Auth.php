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
        
        if ($session->get('userID', false) === false)
            return false;
        
        if ($session->get('userSecret', false) === false)
            return false;
        
        $date = date('Y-m-d H:i:s');

        if ($session->get('exp', $date) <= $date)
            return false;
        
        $userModel = new UserModel();
        
        $search = [
            'user_id' => $session->get('userID'),
            'email' => $session->get('userEmail'),
            'secret' => $session->get('userSecret')
        ];

        if ($userModel->exists($search) === false)
        {
            $session->cleanData();
            return false;
        }
        $session->set('exp', date('Y-m-d H:i:s', strtotime('+30 minutes')));
        return true;
        
    }
    
    public static function signIn(string $email,string $password) 
    {
        $userModel = new UserModel();
        
        $user = $userModel->getSingle(['user_id', 'secret'],[
            'email' => $email,
            'password' => Data::hashPassword($password, $email)
        ]);
        
        $session = Application::$APP->session;

        if ($user !== false) 
        {
           $session->set('userID', $user['user_id']);
           $session->set('userSecret', $user['secret']);
           $session->set('userEmail', $email);
           $session->set('role', Application::USER);
           $session->set('exp', date('Y-m-d H:i:s', strtotime('+30 minutes')));
            
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