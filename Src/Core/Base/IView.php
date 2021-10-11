<?php

namespace Kantodo\Core\Base;

/**
 * Interface view
 */
interface IView
{
    /**
     * Vyrendruje view
     *
     * @param   array<mixed>  $params  parametry z url
     *
     * @return  void
     */
    public function render(array $params = []);
}