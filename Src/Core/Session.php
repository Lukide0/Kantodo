<?php

namespace Kantodo\Core;

/**
 * Session
 */
class Session
{
    /**
     * @var bool
     */
    private $regenerated = false;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * Začne session
     *
     * @return  void
     */
    public function start()
    {
        if (!$this->started) {
            $this->configure();

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $this->initialize();
        }
    }

    /**
     * Inicializace
     *
     * @return  void
     */
    private function initialize()
    {
        $this->started = true;

        $kan = &$_SESSION['__KAN'];

        if (!is_array($kan)) {
            $kan = [];
        }

        $kan['META']['LAST_ACCESS'] = time();

        if (empty($kan['META']['USER_AGENT'])) {
            $kan['META']['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        }

        foreach ($kan['FLASH'] ?? [] as $key => $message) {
            $kan['FLASH'][$key]['remove'] = true;
        }
    }

    /**
     * Regeneruje session id
     *
     * @return  void
     */
    public function regenerateID()
    {
        if ($this->regenerated) {
            return;
        }

        session_regenerate_id(true);
    }

    /**
     * Naconfiguruje session
     *
     * @return  void
     */
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

    /**
     * Zkontroluje jestli je klient důvěryhodný
     *
     * @return  bool
     */
    public function isTrusted()
    {
        $kan = &$_SESSION['__KAN'];

        // different browser
        if ($kan['META']['USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }

        // older than 30 minutes
        if ($kan['META']['LAST_ACCESS'] < time() + 30 * 60) {
            return false;
        }

        return true;
    }

    /**
     * Aktualizuje User-Agent
     *
     * @return  void
     */
    public function updateUserAgent()
    {
        $_SESSION['__KAN']['META']['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Získá/vytvoří CSRF token
     *
     * @return  string
     */
    public function getTokenCSRF()
    {
        if (empty($_SESSION['__KAN']['META']['TOKEN'])) {
            return $this->createTokenCSRF();
        }

        return $_SESSION['__KAN']['META']['TOKEN'];
    }

    /**
     * Vytvoří CSRF token
     *
     * @return  string
     */
    public function createTokenCSRF()
    {
        return $_SESSION['__KAN']['META']['TOKEN'] = bin2hex(random_bytes(32));
    }

    /**
     * Ověří jestli je token stejný
     *
     * @param   string  $token  token
     *
     * @return  bool
     */
    public function verifyTokenCSRF(string $token)
    {
        return hash_equals($this->getTokenCSRF(), $token);
    }

    /**
     * Nastaví klíč s hodnotou
     *
     * @param   string  $key    klíč
     * @param   mixed  $value   hodnota
     * @param   int     $exp    expirace
     *
     * @return  void
     */
    public function set(string $key, $value, int $exp = -1)
    {
        $_SESSION['__KAN']['DATA'][$key] = ['value' => $value, 'exp' => $exp];
    }

    /**
     * Nastaví klíč s hodnotou v již existujcím klíči
     *
     * @param   string  $key        klíč
     * @param   string|null   $innerKey   klíč v klíči
     * @param   mixed  $value       hodnota
     *
     * @return  void
     */
    public function setInside(string $key, $innerKey, $value)
    {
        if (!isset($_SESSION['__KAN']['DATA'][$key])) {
            return;
        }

        if ($innerKey == null) {
            $_SESSION['__KAN']['DATA'][$key]['value'] = $value;
        } else {
            $_SESSION['__KAN']['DATA'][$key]['value'][$innerKey] = $value;
        }

    }

    /**
     * Nastaví expiraci
     *
     * @param   string  $key  klíč
     * @param   int     $exp  expirace
     *
     * @return  void
     */
    public function setExpiration(string $key, int $exp = -1)
    {
        if (isset($_SESSION['__KAN']['DATA'][$key])) {
            $_SESSION['__KAN']['DATA'][$key]['exp'] = $exp;
        }

    }

    /**
     * Získá expiraci klíče
     *
     * @param   string  $key  klíč
     *
     * @return  int
     */
    public function getExpiration(string $key)
    {
        if (isset($_SESSION['__KAN']['DATA'][$key])) {
            return $_SESSION['__KAN']['DATA'][$key]['exp'];
        }

        return 0;
    }

    /**
     * Získá hodnotu podle klíče
     *
     * @param   string  $key       klíč
     * @param   mixed  $fallback   fallback
     *
     * @return  mixed              vrací fallback, pokud neexistuje klíč nebo je po expiraci
     */
    public function get(string $key, $fallback = null)
    {
        if (!isset($_SESSION['__KAN']['DATA'][$key])) {
            return $fallback;
        }

        $exp = $_SESSION['__KAN']['DATA'][$key]['exp'];
        if ($exp != -1 && $exp <= time()) {
            return $fallback;
        }

        return $_SESSION['__KAN']['DATA'][$key]['value'];
    }

    /**
     * Zjistí jestli klíč existuje
     *
     * @param   string  $key  klíč
     *
     * @return  bool
     */
    public function contains(string $key)
    {
        return isset($_SESSION['__KAN']['DATA'][$key]);
    }

    /**
     * Přidá flash zprávu
     *
     * @param   string  $key    klíč
     * @param   mixed  $value   hodnota
     *
     * @return  void
     */
    public function addFlashMessage(string $key, $value)
    {
        $_SESSION['__KAN']['FLASH'][$key] = [
            'remove' => false,
            'value'  => $value,
        ];
    }

    /**
     * Získá flash zprávu podle klíče
     *
     * @param   string  $key       klíč
     * @param   mixed  $fallback   fallback
     *
     * @return  mixed              vrací fallback, pokud neexistuje klíč
     */
    public function getFlashMessage(string $key, $fallback = null)
    {
        return $_SESSION['__KAN']['FLASH'][$key]['value'] ?? $fallback;
    }

    /**
     * Zavře session
     *
     * @return  void
     */
    public function close()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $this->started = false;
        $this->clean();
        session_write_close();
    }

    /**
     * Zničí session
     *
     * @return  void
     */
    public function destroy()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $this->started = false;
        session_destroy();
    }

    /**
     * Smaže data
     *
     * @return  void
     */
    public function cleanData(string $key = null)
    {
        if ($key === null) {
            foreach ($_SESSION['__KAN']['DATA'] as $key => $__) {
                unset($_SESSION['__KAN']['DATA'][$key]);
            }
        } else {
            unset($_SESSION['__KAN']['DATA'][$key]);
        }
    }

    /**
     * Vyčistí session
     *
     * @return  void
     */
    public function clean()
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION)) {
            return;
        }

        $kan = &$_SESSION['__KAN'];

        // remove meta
        foreach ($kan['META'] ?? [] as $key => $__) {
            if (empty($kan['META'][$key])) {
                unset($kan['META'][$key]);
            }

        }
    }

    public function __destruct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION)) {
            return;
        }

        $this->clean();

        foreach ($_SESSION['__KAN']['FLASH'] ?? [] as $key => $message) {
            if ($message['remove'] === true) {
                unset($_SESSION['__KAN']['FLASH'][$key]);
            }

        }
    }
}
