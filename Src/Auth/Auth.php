<?php

namespace Kantodo\Auth;

use Kantodo\Core\BaseApplication;
use Kantodo\Core\IAuth;
use Kantodo\Models\UserModel;

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

        $search = [
            'user_id' => $session->get('user')['id'],
            'email'   => $session->get('user')['email'],
            'secret'  => $session->get('user')['secret'],
        ];

        if ($userModel->exists($search) === false) {
            $session->cleanData();
            return false;
        }
        $session->setExpiration('user', time() + self::EXP);
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
    public static function signIn(string $email, string $password)
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

            $session->set("user", $user, time() + self::EXP);

            return true;
        }

        return false;
    }

    /**
     * Odhlásí uživatele a vymaže všechny data z session
     *
     * @return  void
     */
    public static function signOut()
    {
        BaseApplication::$APP->session->cleanData('user');
    }
}
