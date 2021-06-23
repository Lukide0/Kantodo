<?php 
namespace Kantodo\Core;


abstract class ViewLang implements IView
{
    protected $lang;
    public function __construct() {
        $this->lang = new Lang();
    }
}


?>