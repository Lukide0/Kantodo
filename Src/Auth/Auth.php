<?php

namespace Kantodo\Auth;

use DateTime;
use Kantodo\Core\Application;
use Kantodo\Core\BaseApplication;
use Kantodo\Core\IAuth;
use Kantodo\Models\UserModel;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\Purpose;

/**
 * Auth
 */
class Auth implements IAuth
{
    const EXP = 60 * 30;
    /**
     * Hash hesla
     *
     * @param   string  $password  heslo
     * @param   string  $salt      "sůl"
     *
     * @return  string
     */
    public static function hashPassword(string $password, string $salt = '')
    {
        $middle = (int) floor(strlen($salt) / 2);

        $password = substr($salt, 0, $middle) . $password . substr($salt, $middle);

        return hash('sha256', $password);
    }

    /**
     * Zkontroluje jesli je uživatel přihlášen
     *
     * @return  bool
     */
    public static function isLogged(): bool
    {
        $session = BaseApplication::$APP->session;

        if ($session->getExpiration('user') <= time()) {
            return false;
        }
        $userModel = new UserModel();
        $secret = $session->get('user')['secret'];
        $userId = $session->get('user')['id'];

        $search = [
            'user_id' => $userId,
            'email'   => $session->get('user')['email'],
            'secret'  => $secret,
        ];

        if ($userModel->exists($search) === false) {
            self::signOut();
            return false;
        }

        $keyMaterial = Application::getSymmetricKey();
        $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');
        $expirationUnix = $expiration->getTimestamp();

        if ($keyMaterial === false)
            return false;

        $key = new SymmetricKey($keyMaterial);
        $paseto = (new Builder())
            ->setVersion(new Version4)
            ->setPurpose(Purpose::local())
            ->setKey($key)
            // nastavení dat
            ->setClaims([
                'secret' => $secret
            ])
            // nastavení expirace
            ->setIssuedAt()
            ->setExpiration($expiration)
            // nastavení předmětu
            ->setSubject('auth');
        
        setcookie('token', $paseto->toString(), $expirationUnix, "/");
        $session->setExpiration('user', $expirationUnix);
        return true;
    }

    /**
     * Přihlásí uživatele
     *
     * @param   string  $email     email
     * @param   string  $password  helso
     *
     * @return  bool               vrací false, pokud neexistuje
     */
    public static function signIn(string $email, string $password): bool
    {
        $userModel = new UserModel();

        $user = $userModel->getSingle(['user_id' => 'id', 'secret', 'firstname', 'lastname'], [
            'email'    => $email,
            'password' => Auth::hashPassword($password, $email),
        ]);
        $session = BaseApplication::$APP->session;

        if ($user !== false) {
            $user['email'] = $email;
            $user['role']  = BaseApplication::USER;

            $keyMaterial = Application::getSymmetricKey();
            $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');
            $expirationUnix = $expiration->getTimestamp();
            
            if ($keyMaterial === false)
                return false;

            $key = new SymmetricKey($keyMaterial);
            $paseto = (new Builder())
                ->setVersion(new Version4)
                ->setPurpose(Purpose::local())
                ->setKey($key)
                // nastavení dat
                ->setClaims([
                    'secret' => $user['secret']
                ])
                // nastavení expirace
                ->setIssuedAt()
                ->setExpiration($expiration)
                // nastavení předmětu
                ->setSubject('auth');
            
            setcookie('token', $paseto->toString(), $expirationUnix, "/");
            $session->set("user", $user, $expirationUnix);

            return true;
        }

        return false;
    }

    /**
     * Odhlásí uživatele a vymaže všechny data z session
     *
     * @return  void
     */
    public static function signOut(): void
    {
        unset($_COOKIE['token']); 
        setcookie('token', "", -1, '/'); 
        BaseApplication::$APP->session->cleanData('user');
    }
}
