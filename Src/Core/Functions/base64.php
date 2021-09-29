<?php

namespace Kantodo\Core\Functions;

// https://www.php.net/manual/en/function.base64-encode.php#123098
function base64EncodeUrl($string)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
}

function base64DecodeUrl($string)
{
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
}
