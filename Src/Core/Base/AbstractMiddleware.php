<?php

declare(strict_types = 1);

namespace Kantodo\Core\Base;

/**
 * Základní middleware
 */
abstract class AbstractMiddleware
{
    /**
     * Provede middleware
     *
     * @param   array<mixed>  $params  parametry
     *
     * @return  array<mixed> upravené parametry
     */
    abstract public function execute(array $params = []);
}
