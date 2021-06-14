<?php 


namespace Kantodo\Core;

use Kantodo\Core\Exception\NotAuthorizedException;

class AuthMiddleware extends BaseMiddleware
{
    public function Execute()
    {
        $role = Application::GetRole();
        if (in_array($role, Application::$APP->controller->access)) 
        {
            return;
        }

        throw new NotAuthorizedException();
    }    
}


?>