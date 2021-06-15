<?php 
namespace Kantodo\Core;


abstract class AuthController extends Controller
{
    public function __construct() {
        $this->RegisterMiddleware(new AuthMiddleware());
    }
}