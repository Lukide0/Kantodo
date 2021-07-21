<?php

namespace Kantodo\Core;

class Session
{
    private $regenerated = false;
    private $started = false;

    public function start()
    {
        if (!$this->started) 
        {
            $this->configure();

            if (session_status() !== PHP_SESSION_ACTIVE)
                session_start();

            $this->initialize();
        }
    }

    private function initialize() 
    {
        $this->started = true;

        $kan = &$_SESSION['__KAN'];

        if (!is_array($kan)) 
            $kan = [];

            $kan['META']['LAST_ACCESS'] = time();
            
            
            
        if (empty($kan['META']['USER_AGENT']))
            $kan['META']['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        

        foreach ($kan['FLASH'] ?? [] as $key => $message) {
            $kan['FLASH'][$key]['remove'] = true;
        }
    }

    public function regenerateID()
    {
        if ($this->regenerated)
            return;

        session_regenerate_id(true);
    }

    
    
    private function configure() 
    {
        ini_set('session.referer_check', '');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        
        register_shutdown_function([$this, 'Clean']);
        
    }
    
    public function isTrusted()
    {
        $kan = &$_SESSION['__KAN'];
        
        // different browser
        if ($kan['META']['USER_AGENT'] != $_SERVER['HTTP_USER_AGENT'])
        return false;
        
        // older than 30 minutes
        if ($kan['META']['LAST_ACCESS'] < time() + 30*60)
        return false;
        
        return true;
    }
    
    public function getTokenCSRF()
    {
        if (empty($_SESSION['__KAN']['META']['TOKEN']))
            return $this->createTokenCSRF();
        return $_SESSION['__KAN']['META']['TOKEN'];
    }

    public function createTokenCSRF()
    {
        return $_SESSION['__KAN']['META']['TOKEN'] = bin2hex(random_bytes(32));
    }

    public function verifyTokenCSRF(string $token)
    {
        return hash_equals($this->getTokenCSRF(), $token);
    }

    public function set(string $key, $value) 
    {
        $_SESSION['__KAN']['DATA'][$key] = $value;
    }

    public function get(string $key, $fallback = NULL) 
    {
        return $_SESSION['__KAN']['DATA'][$key] ?? $fallback;
    }

    public function addFlashMessage(string $key, $value) 
    {
        $_SESSION['__KAN']['FLASH'][$key] = [
            'remove' => false,
            'value' => $value
        ];
    }

    public function getFlashMessage(string $key, $fallback = NULL) 
    {
        return $_SESSION['__KAN']['FLASH'][$key]['value'] ?? $fallback;
    }

    public function close()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        return;
        
        $this->started = false;
        $this->clean();
        session_write_close();
    }
    
    public function destroy() 
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            return;

        $this->started = false;
        session_destroy();
    }

    public function cleanData()
    {
        foreach ($_SESSION['__KAN']['DATA'] as $key => $__) {
            unset($_SESSION['__KAN']['DATA'][$key]);
        }
    }


    public function clean() 
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION))
            return;

        $kan = &$_SESSION['__KAN'];

        // remove meta
        foreach ($kan['META'] ?? [] as $key => $__) {
            if (empty($kan['META'][$key]))
                unset($kan['META'][$key]);
        }
    }

    public function __destruct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION))
            return;

        $this->clean();


        foreach ($_SESSION['__KAN']['FLASH'] ?? [] as $key => $message) {
            if ($message['remove'] === true)
                unset($_SESSION['__KAN']['FLASH'][$key]);
        }




    }
}



?>