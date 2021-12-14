<?php

declare(strict_types = 1);

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
     * Paseto token nezpracovaný
     *
     * @var string|false
     */
    static $PASETO_RAW = false;
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
     * @param   string  $id  id uživatele
     * @param   string  $secret  secret
     * @param   string  $email  email
     * @param   string  $role  role
     * @param   DateTime  $expiration  expirace
     *
     * @return  string|null       Vrací null v případě, že se nepodařilo načíst klíč
     */
    public static function createToken(string $id, string $secret, string $email, string $role, DateTime $expiration)
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
                'id' => $id,
                'secret' => $secret,
                'role' => $role,
                'email' => $email
            ])
            // nastavení expirace
            ->setNotBefore()
            ->setIssuedAt()
            ->setExpiration($expiration)
            // nastavení předmětu
            ->setSubject(self::SUBJECT)
            ->toString();

        return $paseto;
    }

    /**
     * Získá bearer token (paseto)
     *
     * @return  string|false  token
     */
    public static function getBearerToken()
    {
        // https://stackoverflow.com/a/40582472
        $headers = (isset($_SERVER['Authorization'])) ? trim($_SERVER['Authorization']) : ((isset($_SERVER['HTTP_AUTHORIZATION'])) ? trim($_SERVER["HTTP_AUTHORIZATION"]) : false);

        if ($headers === false && function_exists('apache_request_headers')) 
        {
            $requestHeaders = apache_request_headers();
            /** @phpstan-ignore-next-line */
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

            /** @phpstan-ignore-next-line */
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if ($headers === false)
            return $headers;

        if (preg_match('/Bearer\s(\S+)/', $headers, $matches) !== false)
            return $matches[1];

        return false;
    }

    /**
     * Získá PASETO z požadavku
     *
     * @return  string|false  token
     */
    public static function getPasetoTokenFromRequest()
    {
        $paseto = $_COOKIE[self::COOKIE_KEY] ?? false;
        
        if ($paseto === false) 
            $paseto = self::getBearerToken();
        
        self::$PASETO_RAW = $paseto;
        return $paseto;
    }

    /**
     * Dekoduje paseto token
     *
     * @param   string  $token  token
     *
     * @return  bool            Vrací status zpracování tokenu
     */
    public static function checkToken(string $token, string $subject = self::SUBJECT)
    {
        $key = BaseApplication::getSymmetricKey();

        if ($key === false)
            return false;
        
        
        $parser = Parser::getLocal(new SymmetricKey($key), ProtocolCollection::v4())
            ->addRule(new ValidAt)
            ->addRule(new Subject($subject));
        
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
        $paseto = self::getPasetoTokenFromRequest();

        // KROK A.2 - zkontrolovat jestli je validní
        if ($paseto !== false && self::checkToken($paseto)) 
            return true;
        else 
            return false;
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

        $user = $userModel->getSingle(['user_id'], ['email' => $email, 'secret' => $secret]);

        // Uživatel neexistuje
        if ($user === false) {
            return false;
        }

        $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');
        return self::createToken($user['user_id'], $secret, $email, (string)BaseApplication::USER, $expiration);
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

        $user = $userModel->getSingle(['user_id', 'secret'], [
            'email'    => $email,
            'password' => Auth::hashPassword($password, $email),
        ]);

        if ($user !== false) {
            $expiration = (new DateTime())->modify('+' . self::EXP . ' seconds');
            $expirationUnix = $expiration->getTimestamp();
    
            $paseto = self::createToken($user['user_id'], $user['secret'], $email, (string)BaseApplication::USER, $expiration);
            
            if ($paseto === null)
                return false;

            setcookie(self::COOKIE_KEY, $paseto, $expirationUnix, "/");
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
        BaseApplication::$BASE_APP->session->cleanData();
    }

    /**
     * Získá data z paseto
     *
     * @return  array<string,string>|null  data o uživateli nebo NULL
     */
    public static function getUser()
    {
        if (self::$PASETO === false)
            return null;

        return self::$PASETO->getClaims();
    }
}
