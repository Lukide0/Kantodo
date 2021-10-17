<?php

namespace Kantodo\Core\Functions;

/**
 * https://www.php.net/manual/en/function.base64-encode.php#123098
 *
 * @param   string  $string
 *
 * @return  string
 */
function base64EncodeUrl(string $string)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
}

/**
 * https://www.php.net/manual/en/function.base64-encode.php#123098
 *
 * @param   string  $string
 *
 * @return  string
 */
function base64DecodeUrl(string $string)
{
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
}
