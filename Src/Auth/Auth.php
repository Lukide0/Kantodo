<?php

namespace Kantodo\Auth;

use DateTime;
use Kantodo\Core\Application;
use Kantodo\Core\BaseApplication;
use Kantodo\Core\IAuth;
use Kantodo\Models\UserModel;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\JsonToken;
use ParagonIE\Paseto\Keys\Version4\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Rules\Subject;
use ParagonIE\Paseto\Rules\ValidAt;

/**
 * Auth
 */
class Auth implements IAuth
{
    const EXP = 60 * 30;
    const SUBJECT = 'KANTODO_AUTH';
    const COOKIE_KEY = 'KANTODO_AUTH_TOKEN';

    /**
     * Paseto token
     *
     * @var JsonToken|false
     */
    static $PASETO = false;
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
     * Vytvoří public paseto token
     *
     * @param   string  $secret  secret
     *
     * @return  string|null       Vrací null v případě, že se nepodařilo načíst klíč
     */
    public static function getToken(string $secret, DateTime $expiration)
    {
        $keyMaterial = Application::getSymmetricKey();

        if ($keyMaterial === false)
            return null;

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
            ->setSubject(self::SUBJECT)
            ->toString();

        return $paseto;
    }

    /**
     * Dekoduje paseto token
     *
     * @param   string  $token  token
     *
     * @return  bool            Vrací status zpracování tokenu
     */
    public static function checkToken(string $token)
    {
        $key = BaseApplication::getSymmetricKey();

        if ($key === false)
            return false;
        
        
        $parser = Parser::getLocal(new SymmetricKey($key), ProtocolCollection::v4())
            ->addRule(new ValidAt)
            ->addRule(new Subject(self::SUBJECT));
        
        try {
            self::$PASETO = $parser->parse($token);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    /**
     * Zkontroluje jesli je uživatel přihlášen
     *
     * @return  bool
     */
    public static function isLogged(): bool
    {
        // KROK A.1 - zkontrolovat jestli má uživatel paseto token
        $paseto = $_COOKIE[self::COOKIE_KEY] ?? false;

        // KROK A.2 - zkontrolovat jestli je validní
        if ($paseto !== false && self::checkToken($paseto)) 
            return true;

        // KROK B.1 - získat session
        $session = BaseApplication::$APP->session;

        // KROK B.2 - zkontrolovat expiraci
        if ($session->getExpiration('user') > time())
            return true;
        
        return false;
    }

    /**
     * Obnoví paseto token a session pouze v případě, že je nastavená session
     *
     * @return  void
     */
    public static function refreshBySession()
    {
        $session = BaseApplication::$APP->session;

        if ($session->getExpiration('user') <= time())
            return;

        $userModel = new UserModel();
        $secret = $session->get('user')['secret'];
        $userId = $session->get('user')['id'];

        $search = [
            'user_id' => $userId,
            'email'   => $session->get('user')['email'],
            'secret'  => $secret,
        ];

        // Uživatel neexistuje
        if ($userModel->exists($search) === false) {
            self::signOut();
            return;
        }

        $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');
        $expirationUnix = $expiration->getTimestamp();

        $paseto = self::getToken($secret, $expiration);
        
        if ($paseto === null)
            return;
        
        setcookie(self::COOKIE_KEY, $paseto, $expirationUnix, "/");
        $session->setExpiration('user', $expirationUnix);
    }

    /**
     * Obnoví token pomocí emailu a secret
     *
     * @param   string  $email   email
     * @param   string  $secret  secret
     *
     * @return  string|false|null     Vrací nový token. Pokud uživatel neexistuje vrací false nebo pokud se nepodařilo vytvořit token, tak je vráceno null
     */
    public static function refreshByCredentials(string $email, string $secret)
    {
        $userModel = new UserModel();
        $search = [
            'email' => $email,
            'secret' => $secret,
        ];

        // Uživatel neexistuje
        if ($userModel->exists($search) === false) {
            return false;
        }

        $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');

        return self::getToken($secret, $expiration);
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

            $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');
            $expirationUnix = $expiration->getTimestamp();
    
            $paseto = self::getToken($user['secret'], $expiration);
            
            if ($paseto === null)
                return false;

            setcookie('token', $paseto, $expirationUnix, "/");
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
        unset($_COOKIE[self::COOKIE_KEY]); 
        setcookie(self::COOKIE_KEY, "", -1, '/'); 
        BaseApplication::$APP->session->cleanData('user');
    }
}
