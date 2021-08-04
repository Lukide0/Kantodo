<?php 

namespace Kantodo\Core;



// https://www.php.net/manual/en/function.base64-encode.php#123098
function base64_encode_url($string) 
{
    return str_replace(['+','/','='], ['-','_',''], base64_encode($string));
}

function base64_decode_url($string) 
{
    return base64_decode(str_replace(['-','_'], ['+','/'], $string));
}


?>