<?php

namespace Kantodo\Core\Validation;

/**
 * Validace data v array
 */
class Data
{
    final private function __construct()
    {
    }

    /**
     * Vrací prázdné klíče
     *
     * @param   array<mixed>  $data  předmět
     * @param   array<mixed>  $keys  klíče
     *
     * @return  array<mixed>         klíče
     */
    function empty(array $data, array $keys) {

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
     * @param   array<mixed>  $data  předmět
     * @param   array<mixed>  $keys  klíče
     *
     * @return  bool          vrací true pokud je alespoň 1 klíč prázdný
     */
    public static function isEmpty(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (empty($data[$key])) {
                return true;
            }

        }

        return false;
    }

    /**
     * Vrací klíče, které nejsou nastaveny
     *
     * @param   array<mixed>  $data  předmět
     * @param   array<mixed>  $keys  klíče
     *
     * @return  array<mixed>         klíče
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
     * @param   array<mixed>  $data  předmět
     * @param   array<mixed>  $keys  klíče
     * @param   mixed  $value hodnota
     *
     * @return void
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
     * @param   array<mixed>  $data  předmět
     * @param   array<mixed>  $keys  klíče
     * @param   mixed  $value hodnota
     *
     * @return void
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
     * Vrátí klíče v array, které mají stejnou hodnotu
     *
     * @param   array<mixed>  $data  předmět
     * @param   array<mixed>  $keys  klíče, pokud je hodnota null, tak jsou všechny klíče kontrolovány
     * @param   bool   $strict pokud je 'true', tak jsou kontrolovány pouze zadané klíče mezi sebou
     * 
     * @return  array<string|int,array<string>>    duplicitní klíče ve formátu ```[klicA => [klicB, klicC, ...], ...]```
     */
    public static function duplicate(array $data, array $keys = null, bool $strict = false)
    {
        if ($keys == null)
            $keys = array_keys($data);

        $duplicit = [];
        if ($strict) {

            foreach ($keys as $a) {
                foreach ($keys as $b) {
                    if ($a == $b)
                        continue;
                    
                    if ($data[$a] == $data[$b] && empty($duplicit[$b])) {
                        $duplicit[$a][] = $b;
                    }
                }
            }

            return $duplicit;
        }

        foreach ($keys as $a) {
            foreach ($data as $b => $value) {
                if ($a == $b) {
                    continue;
                }

                if ($data[$a] == $value && empty($duplicit[$b])) {
                    $duplicit[$a][] = $b;
                }
            }
        }

        return $duplicit;

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
        if (strlen($password) == 0) {
            return false;
        }

        if ($mustContainNumber && !preg_match('/[0-9]/', $password)) {
            return false;
        }

        if ($mustContainSpecialChar && !preg_match('/[`!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?~]/', $password)) {
            return false;
        }

        if ($mustContainUppercaseChar && !preg_match('/[A-Z]/', $password)) {
            return false;
        }

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
        return filter_var($email, FILTER_VALIDATE_EMAIL) != false;
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
        if (strlen($name) == 0) {
            return false;
        }

        // jméno obsahuje mezeru
        if (strpos($name, ' ')) {
            return false;
        }

        // velké 1. písmeno
        $name = ucfirst(strtolower($name));
        return $name;
    }

    /**
     * Zkontroluje jestli je url externí
     *
     * @param   string  $url
     *
     * @return  bool
     */
    public static function isURLExternal(string $url)
    {
        $link = parse_url($url);
        $home = parse_url($_SERVER['HTTP_HOST']);

        /** @phpstan-ignore-next-line */
        if (empty($link['host'])) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        if ($link['host'] == $home['host']) {
            return false;
        }

        return true;
    }
}
