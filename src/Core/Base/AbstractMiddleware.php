<?php

namespace Kantodo\Core\Base;

/**
 * Základní middleware
 */
abstract class AbstractMiddleware
{
    /**
     * Provede middleware
     *
     * @param   array  $params  parametry
     *
     * @return  void
     */
    abstract public function execute(array $params = []);
}
