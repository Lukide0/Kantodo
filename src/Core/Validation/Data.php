<?php

namespace Kantodo\Core\Validation;

/**
 * Validace data v array
 */
class Data
{
    private final function __construct()
    {
    }

    /**
     * Vrací prázdné klíče
     *
     * @param   array  $data  předmět
     * @param   array  $keys  klíče
     *
     * @return  array         klíče
     */
    public static function empty(array $data, array $keys)
    {

        $emptyKeys = [];
        foreach ($keys as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                $emptyKeys[] = $key;
            }
        }

        return $emptyKeys;
    }

    /**
     * Kontrola jestli je klíč prázdný
     *
     * @param   array  $data  předmět
     * @param   array  $keys  klíče
     *
     * @return  bool          vrací true pokud je alespoň 1 klíč prázdný
     */
    public static function isEmpty(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (empty($data[$key]))
                return true;
        }

        return false;
    }

    /**
     * Vrací klíče, které nejsou nastaveny
     *
     * @param   array  $data  předmět
     * @param   array  $keys  klíče
     *
     * @return  array         klíče
     */
    public static function notSet(array $data, array $keys)
    {
        $notSetKeys = [];
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                $notSetKeys[] = $key;
            }
        }
        return $notSetKeys;
    }

    /**
     * Nastaví klíč pokud není nastaven
     *
     * @param   array  $data  předmět
     * @param   array  $keys  klíče
     * @param   mixed  $value hodnota
     *
     */
    public static function setIfNotSet(array &$data, array $keys, $value)
    {
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }
    }

    /**
     * Nastavý klíče pokud jsou prázdné
     *
     * @param   array  $data  předmět
     * @param   array  $keys  klíče
     * @param   mixed  $value hodnota
     *
     */
    public static function fillEmpty(array &$data, array $keys, $value)
    {
        foreach ($keys as $key) {
            if (empty($data[$key])) {
                $data[$key] = $value;
            }
        }
    }

    /**
     * Kontrola hesla jestli je validní 
     *
     * @param   string  $password                  heslo
     * @param   bool    $mustContainNumber         musí obsahova číslo
     * @param   bool    $mustContainSpecialChar    musí obsahova speciální znak
     * @param   bool    $mustContainUppercaseChar  musí obsahova velké písmeno
     *
     * @return  bool
     */
    public static function isValidPassword(string $password, bool $mustContainNumber = false, bool $mustContainSpecialChar = false, bool $mustContainUppercaseChar = false)
    {
        if (strlen($password) == 0)
            return false;

        if ($mustContainNumber && !preg_match('/[0-9]/', $password))
            return false;

        if ($mustContainSpecialChar && !preg_match('/[`!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?~]/', $password))
            return false;

        if ($mustContainUppercaseChar && !preg_match('/[A-Z]/', $password))
            return false;
        return true;
    }

    /**
     * Kontrola jesli je email validní
     *
     * @param   string  $email  email
     *
     * @return  bool
     */
    public static function isValidEmail(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

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
        $middle = floor(strlen($salt) / 2);

        $password = substr($salt, 0, $middle) . $password . substr($salt, $middle);

        return hash('sha256', $password);
    }

    /**
     * Naformátuje jméno
     *
     * @param   string  $name  jméno
     *
     * @return  string|false   vrací false, pokud je $name není jméno
     */
    public static function formatName(string $name)
    {
        // odstranění space a tab
        $name = trim($name);

        // pokud obsahuje jen space a tab
        if (strlen($name) == 0)
            return false;

        // jméno obsahuje mezeru
        if (strpos($name, ' '))
            return false;

        // velké 1. písmeno
        $name = ucfirst(strtolower($name));
        return $name;
    }

    public static function isURLExternal(string $url)
    {
        $link = parse_url($url);
        $home = parse_url($_SERVER['HTTP_HOST']);
        if (empty($link['host']))
            return false;

        if ($link['host'] == $home['host'])
            return false;

        return true;
    }
}
