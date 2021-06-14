<?php

namespace Kantodo\Core;

class Model
{
    protected $values = [];

    public function Get(string $name)
    {
        return $this->values[$name] ?? null; 
    }

    public function Set(string $name, mixed $value) 
    {
        $this->values[$name] = $value;
    }
}



?>


