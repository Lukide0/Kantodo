<?php

declare(strict_types = 1);

namespace Kantodo\Core\Validation;


/**
 * Kontrola hodnoty
 */
class DataType
{
    final private function __construct()
    {
    }

    /**
     * Kontrola jestli je hodnota číslo
     *
     * @param   mixed $value  hodnota
     * @param   int  $min     min
     * @param   int  $max     max
     *
     * @return  bool
     */
    public static function number($value, int $min = null, int $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }

        return self::inRange($value, $min, $max);
    }

    /**
     * Kontrola jestli je hodnota celé číslo
     *
     * @param   mixed $value  hodnota
     * @param   int  $min    min
     * @param   int  $max    max
     *
     * @return  bool
     */
    public static function wholeNumber($value, int $min = null, int $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }

        if ($value % 1 != 0) {
            return false;
        }

        return self::inRange($value, $min, $max);
    }

    /**
     * Kontrola jestli je číslo v rozmezí
     *
     * @param   float|int|string $number   číslo
     * @param   int  $min     min
     * @param   int  $max     max
     *
     * @return  bool
     */
    public static function inRange($number, int $min = null, int $max = null)
    {
        if ($min !== null && $number <= $min) {
            return false;
        }

        if ($max !== null && $number >= $max) {
            return false;
        }

        return true;
    }
}
