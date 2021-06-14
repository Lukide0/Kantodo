<?php 
namespace Kantodo\Core;

use InvalidArgumentException;

abstract class AuthController extends Controller
{
    public function __construct() {
        $this->RegisterMiddleware(new AuthMiddleware());
    }
}