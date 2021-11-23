<?php

declare(strict_types = 1);

namespace Kantodo\Core;

interface IAuth
{
    public static function isLogged(): bool;
}
