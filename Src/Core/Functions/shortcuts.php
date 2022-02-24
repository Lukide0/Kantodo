<?php

declare(strict_types=1);

namespace Kantodo\Core\Functions;

use Kantodo\Core\BaseApplication;

/**
 * Zkratka pro překlady
 *
 * @param   string  $name   slovo
 * @param   string  $group  skupina
 *
 * @return  string          slovo přeložené
 */
function t(string $name, string $group = 'global')
{
    return BaseApplication::$BASE_APP->lang->get($name, $group);
}
