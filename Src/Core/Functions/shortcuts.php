<?php 

namespace Kantodo\Core\Functions;

use Kantodo\Core\Application;

/**
 * Zkratka pro překlady
 *
 * @param   string  $name   slovo
 * @param   string  $group  skupina
 *
 * @return  string          slovo přeložené
 */
function t_(string $name, string $group = 'global')
{
    return Application::$APP->lang->get($name, $group);
}

?>